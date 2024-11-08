<?php

namespace Hametuha\QueryHighJack;


use Hametuha\Model\Author;
use WPametu\API\QueryHighJack;

/**
 * Class ProfileQuery
 * @package Hametuha\Rest
 * @property-read Author $authors
 */
class ProfileQuery extends QueryHighJack {


	/**
	 * @var string
	 */
	protected $pseudo_post_type = 'profile';

	/**
	 * Query vars
	 *
	 * @var array
	 */
	protected $query_var = [ 'profile_name' ];

	protected $models = [
		'authors' => Author::class,
	];


	/**
	 * リライトルール
	 *
	 * @var array
	 */
	protected $rewrites = [
		'authors/search/page/(\d+)/?$'              => 'index.php?profile_name=0&paged=$matches[1]',
		'authors/search/?$'                         => 'index.php?profile_name=0',
		'(series|anpi)/by/([^/]+)/page/([0-9]+)/?$' => 'index.php?post_type=$matches[1]&author_name=$matches[2]&paged=$matches[3]',
		'(series|anpi)/by/([^/]+)/?$'               => 'index.php?post_type=$matches[1]&author_name=$matches[2]',
	];

	/**
	 * action for pre_get_posts
	 *
	 * @param \WP_Query $wp_query
	 */
	public function pre_get_posts( \WP_Query &$wp_query ) {
		if ( $this->is_valid_query( $wp_query ) ) {
			// テンプレートを変更する
			add_filter( 'template_include', [ $this, 'template_include' ] );
		}
	}

	/**
	 * タイトル変更
	 *
	 * @param string $title
	 * @param string $sep
	 * @param string $sep_location
	 *
	 * @return string
	 */
	public function wp_title( $title, $sep, $sep_location ) {
		return __( '執筆者検索', 'hametuha' ) . " {$sep} " . get_bloginfo( 'name' );
	}

	/**
	 * セレクトフィールドを変える
	 *
	 * @param string $select
	 * @param \WP_Query $wp_query
	 *
	 * @return string
	 */
	public function posts_fields( $select, \WP_Query $wp_query ) {
		if ( $this->is_valid_query( $wp_query ) ) {
			$select = <<<SQL
              {$this->authors->posts}.*,
              '{$this->pseudo_post_type}' AS post_type,
              COUNT({$this->authors->posts}.ID) AS work_count
SQL;
		}

		return $select;
	}

	/**
	 * ユーザーテーブルをくっつける
	 *
	 * @param string $join
	 * @param \WP_Query $wp_query
	 *
	 * @return mixed|string
	 */
	public function posts_join( $join, \WP_Query $wp_query ) {
		if ( $this->is_valid_query( $wp_query ) ) {
			$join .= <<<SQL
            LEFT JOIN {$this->authors->table}
            ON {$this->authors->posts}.post_author = {$this->authors->table}.ID
            LEFT JOIN {$this->authors->usermeta} AS furigana
            ON {$this->authors->posts}.post_author = furigana.user_id AND furigana.meta_key = 'last_name'
SQL;
		}

		return $join;
	}

	/**
	 * 投稿者のIDでまとめる
	 *
	 * @param string $group_by
	 * @param \WP_Query $wp_query
	 *
	 * @return mixed|string
	 */
	public function posts_groupby( $group_by, \WP_Query $wp_query ) {
		if ( $this->is_valid_query( $wp_query ) ) {
			$group_by = <<<SQL
              {$this->authors->posts}.post_author
SQL;
		}

		return $group_by;
	}

	/**
	 * 順序を変更
	 *
	 * @param string $order_by
	 * @param \WP_Query $wp_query
	 *
	 * @return mixed|string
	 */
	public function posts_orderby( $order_by, \WP_Query $wp_query ) {
		if ( $this->is_valid_query( $wp_query ) ) {
			$order_by = <<<SQL
              furigana.meta_value ASC,
              {$this->authors->table}.display_name ASC
SQL;
		}

		return $order_by;
	}

	public function posts_where( $where, \WP_Query $wp_query ) {
		if ( $this->is_valid_query( $wp_query ) ) {
			$profile_name = $wp_query->get( 'profile_name' );
			if ( ! empty( $profile_name ) ) {
				$sql    = <<<EOS
                    AND {$this->authors->table}.user_nicename = %s
EOS;
				$where .= $this->authors->db->prepare( $sql, $profile_name );
			}
		}

		return $where;
	}

	public function posts_search( $search, \WP_Query $wp_query ) {
		if ( $this->is_valid_query( $wp_query ) ) {
			$new_query = [];
			$sql       = <<<SQL
            (
              {$this->authors->table}.display_name LIKE %s
              OR
              furigana.meta_value LIKE %s
            )
SQL;
			if ( $terms = $wp_query->get( 'search_terms' ) ) {
				foreach ( $terms as $term ) {
					$s           = '%' . $term . '%';
					$new_query[] = $this->authors->db->prepare( $sql, $s, $s );
				}
			}
			if ( ! empty( $new_query ) ) {
				$search = ' AND ' . implode( ' AND ', $new_query );
			}
		}

		return $search;
	}

	/**
	 * クエリがプロフィールかどうか
	 *
	 * @param \WP_Query $wp_query
	 *
	 * @return bool
	 */
	protected function is_valid_query( \WP_Query $wp_query ) {
		$profile_name = $wp_query->get( 'profile_name' );

		return '0' === $profile_name || ! empty( $profile_name );
	}


	/**
	 * テンプレートを変更
	 *
	 * @param string $template Template path.
	 * @return string
	 */
	public function template_include( $template ) {
		return get_template_directory() . '/archive-author.php';
	}
}
