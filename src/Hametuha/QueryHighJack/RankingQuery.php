<?php

namespace Hametuha\QueryHighJack;


use WPametu\API\QueryHighJack;

/**
 * ランキング取得用のクエリ
 *
 * @package Hametuha\QueryHighJack
 */
class RankingQuery extends QueryHighJack {


	/**
	 * クエリバー
	 *
	 * @var array
	 */
	protected $query_var = [ 'ranking' ];

	/**
	 * リライトルール
	 *
	 * @var array
	 */
	protected $rewrites = [
		'ranking/([0-9]{4})/([0-9]{2})/([0-9]{2})/page/([0-9]+)/?' => 'index.php?ranking=daily&year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&paged=$matches[4]',
		'ranking/([0-9]{4})/([0-9]{2})/([0-9]{2})/?'       => 'index.php?ranking=daily&year=$matches[1]&monthnum=$matches[2]&day=$matches[3]',
		'ranking/([0-9]{4})/([0-9]{2})/page/([0-9]+)/?'    => 'index.php?ranking=monthly&year=$matches[1]&monthnum=$matches[2]&paged=$matches[3]',
		'ranking/([0-9]{4})/([0-9]{2})/?'                  => 'index.php?ranking=monthly&year=$matches[1]&monthnum=$matches[2]',
		'ranking/([0-9]{4})/page/([0-9]+)/?'               => 'index.php?ranking=yearly&year=$matches[1]&paged=$matches[2]',
		'ranking/([0-9]{4})/?'                             => 'index.php?ranking=yearly&year=$matches[1]',
		'ranking/weekly/([0-9]{4})([0-9]{2})([0-9]{2})/page/([0-9]+)/?$' => 'index.php?ranking=weekly&year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&paged=$matches[4]',
		'ranking/weekly/([0-9]{4})([0-9]{2})([0-9]{2})/?$' => 'index.php?ranking=weekly&year=$matches[1]&monthnum=$matches[2]&day=$matches[3]',
		'ranking/?$'                                       => 'index.php?ranking=top',
	];

	/**
	 * 現在のスコア
	 *
	 * @var array
	 */
	public static $cur_score = [];

	/**
	 * {@inheritDoc}
	 */
	public function __construct() {
		parent::__construct();
		add_filter( 'template_include', function ( $template ) {
			if ( is_ranking() ) {
				$template = get_template_directory() . '/ranking.php';
			}
			return $template;
		} );
	}


	/**
	 * ランキング取得用にpost_requestを書き換え
	 *
	 * @param string $request
	 * @param \WP_Query $wp_query
	 * @return mixed|string
	 */
	public function posts_request( $request, \WP_Query $wp_query ) {
		if ( $this->is_valid_query( $wp_query ) ) {
			// 初期条件
			$wheres = $this->get_wheres( $wp_query );
			// 初期条件を追加してWhere節を作る
			$where_clause = implode( ' AND ', $wheres );
			// オフセット
			$per_page = intval( $wp_query->get( 'posts_per_page' ) ?: get_option( 'posts_per_page' ) );
			$offset   = ( ( max( 1, $wp_query->get( 'paged' ) ) - 1 ) * $per_page );
			$request  = <<<SQL
            SELECT SQL_CALC_FOUND_ROWS
                p.*,
                ranking.pv
            FROM (
                SELECT
                    object_id,
                    SUM(object_value) AS pv
                FROM {$this->db->prefix}wpg_ga_ranking
                WHERE {$where_clause}
                GROUP BY object_id
            ) AS ranking
            INNER JOIN {$this->db->posts} AS p
            ON p.ID = ranking.object_id
            WHERE p.post_status = 'publish'
              AND p.post_type = 'post'
            ORDER BY ranking.pv DESC, p.post_date DESC
            LIMIT {$offset}, {$per_page}
SQL;
		}
		return $request;
	}

	/**
	 * 投稿のランクと上昇値を取得する
	 *
	 * @param array $posts
	 * @param \WP_Query $wp_query
	 * @return array
	 */
	public function the_posts( array $posts, \WP_Query $wp_query ) {
		if ( $this->is_valid_query( $wp_query ) ) {
			$where_clause = implode( ' AND ', $this->get_wheres( $wp_query ) );
			$query        = <<<SQL
                SELECT COUNT(*)
                FROM (
                    SELECT
                        object_id,
                        SUM(object_value) AS pv
                    FROM {$this->db->prefix}wpg_ga_ranking
                    WHERE {$where_clause}
                    GROUP BY object_id
                ) AS ranking
                INNER JOIN {$this->db->posts} AS p
                ON p.ID = ranking.object_id
                WHERE p.post_status = 'publish'
                  AND p.post_type = 'post'
                  AND ranking.pv > %d
SQL;
			$prev_where   = implode( ' AND ', $this->get_wheres( $wp_query, true ) );
			// 先週のPVを取得するクエリ
			$pv_query = <<<SQL
               SELECT SUM(object_value)
               FROM {$this->db->prefix}wpg_ga_ranking
               WHERE object_id = %d AND {$prev_where}
SQL;
			// 先週の順位を取得するクエリ
			$prev_rank_query = <<<SQL
              SELECT COUNT(*)
                FROM (
                    SELECT
                        object_id,
                        SUM(object_value) AS pv
                    FROM {$this->db->prefix}wpg_ga_ranking
                    WHERE {$prev_where}
                    GROUP BY object_id
                ) AS ranking
                INNER JOIN {$this->db->posts} AS p
                ON p.ID = ranking.object_id
                WHERE p.post_status = 'publish'
                  AND p.post_type = 'post'
                  AND ranking.pv > %d
SQL;

			foreach ( $posts as &$post ) {
				// 順位を取得する
				if ( isset( $post->pv ) ) {
					$post->rank = $this->db->get_var( $this->db->prepare( $query, $post->pv ) ) + 1;
				}
				// 先週のPVを取得する
				$prev_pv = $this->db->get_var( $this->db->prepare( $pv_query, $post->ID ) );
				if ( ! $prev_pv ) {
					// なければnull
					$post->transition = null;
				} else {
					// 先週の順位を取得する
					$prev_rank        = $this->db->get_var( $this->db->prepare( $prev_rank_query, $prev_pv ) ) + 1;
					$post->transition = version_compare( $prev_rank, $post->rank );
				}
			}
		}
		return $posts;
	}

	/**
	 * Get where clause
	 *
	 * @param \WP_Query $wp_query
	 * @param bool $previous trueにすると前の期間を取得。相対評価のため。
	 * @return array
	 */
	private function get_wheres( \WP_Query $wp_query, $previous = false ) {
		$wheres = [
			"category = 'general'",
		];
		$year   = $wp_query->get( 'year' );
		$month  = $wp_query->get( 'monthnum' );
		$day    = $wp_query->get( 'day' );
		switch ( $wp_query->get( 'ranking' ) ) {
			case 'yearly':
				if ( $previous ) {
					$year--;
				}
				$wheres[] = $this->db->prepare( 'YEAR(calc_date) = %d', $year );
				break;
			case 'monthly':
				if ( $previous ) {
					$last_month = strtotime( sprintf( '%04d-%02d-01 00:00:00', $year, $month ) ) - 1;
					$year       = date_i18n( 'Y', $last_month );
					$month      = date_i18n( 'n', $last_month );
				}
				$wheres[] = $this->db->prepare( 'YEAR(calc_date) = %d', $year );
				$wheres[] = $this->db->prepare( 'MONTH(calc_date) = %d', $month );
				break;
			case 'daily':
				$day = sprintf( '%s-%s-%s', $year, $month, $day );
				if ( $previous ) {
					$day = date_i18n( 'Y-m-d', strtotime( $day . ' 00:00:00' ) - 1 );
				}
				$wheres[] = $this->db->prepare( 'calc_date = %s', $day );
				break;
			case 'last_week':
				$prev_thursday = strtotime( 'Previous Thursday', current_time( 'timestamp' ) );
				if ( $previous ) {
					$prev_thursday -= 60 * 60 * 24 * 7;
				}
				$sunday   = date_i18n( 'Y-m-d', strtotime( 'Previous Sunday', $prev_thursday ) );
				$monday   = date_i18n( 'Y-m-d', strtotime( 'Previous Monday', strtotime( $sunday . ' 00:00:00' ) ) );
				$wheres[] = $this->db->prepare( 'calc_date <= %s', $sunday );
				$wheres[] = $this->db->prepare( 'calc_date >= %s', $monday );
				break;
			case 'range':
				// TODO: 範囲指定に対応する
				break;
			case 'weekly':
				$sunday = sprintf( '%d-%02d-%02d', $year, $month, $day );
				if ( $previous ) {
					$sunday = date_i18n( 'Y-m-d', strtotime( $sunday . ' 00:00:00' ) - 60 * 60 * 24 * 7 );
				}
				$monday   = date_i18n( 'Y-m-d', strtotime( 'Previous Monday', strtotime( $sunday ) ) );
				$wheres[] = $this->db->prepare( 'calc_date <= %s', $sunday );
				$wheres[] = $this->db->prepare( 'calc_date >= %s', $monday );
				break;
			default:
				$wheres[] = '1 = 2';
				break;
		}
		return $wheres;
	}

	/**
	 * Detect if query var is valid
	 *
	 * @param \WP_Query $wp_query
	 * @return bool
	 */
	protected function is_valid_query( \WP_Query $wp_query ) {
		return in_array( $wp_query->get( 'ranking' ), [ 'yearly', 'monthly', 'daily', 'weekly', 'top', 'last_week' ], true );
	}

	/**
	 * {@inheritdoc}
	 */
	public function wp_title( $title, $sep, $sep_location ) {
		$titles = [];
		switch ( get_query_var( 'ranking' ) ) {
			case 'top':
				$titles[] = '厳粛なランキング';
				break;
			case 'daily':
			case 'monthly':
			case 'yearly':
			case 'weekly':
				$titles[] = $this->get_ranking_title();
				break;
		}
		$titles[] = get_bloginfo( 'name' );
		return implode( ' ' . $sep . ' ', $titles );
	}

	/**
	 * ランキングページのタイトル
	 *
	 * @return string
	 */
	protected function get_ranking_title() {
		$year  = get_query_var( 'year' );
		$month = get_query_var( 'monthnum' );
		$day   = get_query_var( 'day' );
		$title = $year . '年';
		if ( $month ) {
			$title .= sprintf( '%d月', $month );
		}
		if ( $day ) {
			$title .= sprintf( '%d日', $day );
		}
		if ( 'weekly' === get_query_var( 'ranking' ) ) {
			$title .= '付の週間ランキング';
		} else {
			$title .= 'のランキング';
		}
		return $title;
	}
}
