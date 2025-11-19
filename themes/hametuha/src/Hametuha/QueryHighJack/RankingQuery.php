<?php

namespace Hametuha\QueryHighJack;


use WPametu\API\QueryHighJack;

/**
 * 期間ランキング取得用のクエリ
 *
 * @feature-group ranking
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
		'ranking/last-week/?'                              => 'index.php?ranking=last_week',
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
			// オフセットは明示的にして切れされている場合はを除いて最大で10
			$per_page  = $wp_query->get( 'posts_per_page' );
			if ( is_numeric( $per_page ) ) {
				$per_page = min( 10, $per_page );
			} else {
				$per_page = 10;
			}
			$offset  = ( ( max( 1, $wp_query->get( 'paged' ) ) - 1 ) * $per_page );
			// 最小PV閾値を取得（低PVのレコードを除外してパフォーマンス向上）
			$min_pv = $this->get_min_pv( $wp_query );
			$having_clause = $min_pv > 0 ? "HAVING pv >= {$min_pv}" : '';
			// 必要ならcalc_found_rows。
			// todo: いつかなくす
			$calc_found_rows = 1 < hametuha_ranking_max_pagenum( $wp_query ) ? 'SQL_CALC_FOUND_ROWS' : '';
			$request = <<<SQL
            SELECT {$calc_found_rows}
                p.*,
                ranking.pv
            FROM (
                SELECT
                    object_id,
                    SUM(object_value) AS pv
                FROM {$this->db->prefix}wpg_ga_ranking
                WHERE {$where_clause}
                GROUP BY object_id
                {$having_clause}
            ) AS ranking
            INNER JOIN {$this->db->posts} AS p
            ON p.ID = ranking.object_id
            WHERE p.post_status = 'publish'
              AND p.post_type = 'post'
            ORDER BY ranking.pv DESC
            LIMIT {$offset}, {$per_page}
SQL;
		}
		return $request;
	}

	/**
	 * 投稿のランクを取得する
	 *
	 * @param array $posts
	 * @param \WP_Query $wp_query
	 * @return array
	 */
	public function the_posts( array $posts, \WP_Query $wp_query ) {
		if ( $this->is_valid_query( $wp_query ) ) {
			$rank       = ( max( 1, (int) $wp_query->get( 'paged' ) ) - 1 ) * 10;
			$current_pv = (int) $posts[0]->pv;
			$buff       = -1;
			foreach ( $posts as &$post ) {
				// 順位を取得する
				if ( $current_pv > $post->pv ) {
					$rank += $buff + 1;
					$buff = 0;
					$current_pv = (int) $post->pv;
				} elseif ( $current_pv === (int) $post->pv ) {
					++$buff;
				}
				$post->rank = $rank + 1;
			}
		}
		return $posts;
	}

	/**
	 * Get minimum PV threshold for ranking
	 *
	 * @param \WP_Query $wp_query
	 * @return int
	 */
	private function get_min_pv( \WP_Query $wp_query ) {
		switch ( $wp_query->get( 'ranking' ) ) {
			case 'yearly':
				return 100;
			case 'monthly':
				return 50;
			case 'weekly':
				return 10;
			case 'daily':
			case 'last_week':
			default:
				return 0; // 閾値なし
		}
	}

	/**
	 * Get where clause
	 *
	 * @param \WP_Query $wp_query
	 * @param bool $deprecated まえはtrueにすると前の期間を取得していたが、重すぎて廃止。
	 * @return array
	 */
	private function get_wheres( \WP_Query $wp_query, $deprecated = false ) {
		$wheres = [
			"category = 'general'",
		];
		$year   = $wp_query->get( 'year' );
		$month  = $wp_query->get( 'monthnum' );
		$day    = $wp_query->get( 'day' );
		switch ( $wp_query->get( 'ranking' ) ) {
			case 'yearly':
				$year_start = sprintf( '%d-01-01', $year );
				$year_end   = sprintf( '%d-12-31', $year );
				$wheres[] = $this->db->prepare( 'calc_date BETWEEN %s AND %s', $year_start, $year_end );
				break;
			case 'monthly':
				$month_start = sprintf( '%d-%02d-01', $year, $month );
				$month_end   = new \DateTime( $month_start, wp_timezone() );
				$month_end->modify( 'last day of this month' );
				$wheres[] = $this->db->prepare( 'calc_date BETWEEN %s AND %s', $month_start, $month_end->format( 'Y-m-d' ) );
				break;
			case 'daily':
				$day = sprintf( '%04d-%02d-%02d', $year, $month, $day );
				$wheres[] = $this->db->prepare( 'calc_date = %s', $day );
				break;
			case 'last_week':
				// 先週の木曜日を基準に、その週の日曜日（週の終わり）を取得
				$now = new \DateTime( 'now', wp_timezone() );
				$now->modify( 'Previous Thursday' );
				$now->modify( 'Previous Sunday' );
				$sunday = $now->format( 'Y-m-d' );
				// 日曜日の週の月曜日（週の始まり）を取得
				$now->modify( 'Previous Monday' );
				$monday = $now->format( 'Y-m-d' );
				$wheres[] = $this->db->prepare( 'calc_date BETWEEN %s AND %s', $monday, $sunday );
				break;
			case 'range':
				// TODO: 範囲指定に対応する。対応未定。
				break;
			case 'weekly':
				// 指定された日曜日（週の終わり）を基準に、その週の月曜日（週の始まり）を取得
				$sunday_str = sprintf( '%d-%02d-%02d', $year, $month, $day );
				$sunday_obj = new \DateTime( $sunday_str, wp_timezone() );
				$sunday_obj->modify( 'Previous Monday' );
				$monday = $sunday_obj->format( 'Y-m-d' );
				$wheres[] = $this->db->prepare( 'calc_date BETWEEN %s AND %s', $monday, $sunday_str );
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
		if ( ! in_array( $wp_query->get( 'ranking' ), [ 'yearly', 'monthly', 'daily', 'weekly', 'top', 'last_week' ], true ) ) {
			return false;
		}
		// 10ページを超えていたら404にする
		$paged = (int) get_query_var( 'paged' );
		if ( hametuha_ranking_max_pagenum( $wp_query ) < $paged ) {
			return false;
		}
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function wp_title( $title, $sep, $sep_location ) {
		$titles = [ ranking_title() ];
		$cur_page = (int) get_query_var( 'paged' );
		if ( 2 <= $cur_page ) {
			// 2ページ目以降
			$titles[] = sprintf( '%d位〜', ( $cur_page - 1 ) * 10 + 1 );
		}
		$titles []= get_bloginfo( 'name' );
		return implode( ' ' . $sep . ' ', $titles );
	}
}
