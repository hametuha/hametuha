<?php

namespace Hametuha\QueryHighJack;


use WP_Query;
use WPametu\API\QueryHighJack;

/**
 * 歴代ベストを表示する
 *
 * @feature-group ranking
 */
class BestQuery extends QueryHighJack {

	protected $query_var = [ 'ranking' ];

	protected $rewrites = [
		'ranking/best/page/([0-9]+)/?$'         => 'index.php?ranking=best&paged=$matches[1]',
		'ranking/best/?$'                       => 'index.php?ranking=best',
		'ranking/best/([^/]+)/page/([0-9]+)/?$' => 'index.php?ranking=best&category_name=$matches[1]&paged=$matches[2]',
		'ranking/best/([^/]+)/?$'               => 'index.php?ranking=best&category_name=$matches[1]',
	];

	/**
	 * Add meta_query to Query Post
	 *
	 * @param \WP_Query $wp_query
	 */
	public function pre_get_posts( \WP_Query &$wp_query ) {
		if ( $this->is_valid_query( $wp_query ) ) {
			// 最低条件
			$wp_query->set( 'post_type', 'post' );
			$wp_query->set( 'post_status', 'publish' );
			// ベストの場合は上位100件までなので、明示的に指定されていた場合を除き1ページに最大で10件
			$per_page = $wp_query->get( 'posts_per_page' );
			if ( is_numeric( $per_page ) ) {
				$per_page = min( $per_page, 10 );
			} else {
				$per_page = 10;
			}
			$wp_query->set( 'posts_per_page', $per_page );
			// かつ、PVが1000以上
			$wp_query->set( 'orderby', 'meta_value_num' );
			$wp_query->set( 'order', 'DESC' );
			$wp_query->set( 'meta_query', [
				[
					'key'     => '_current_pv',
					'value'   => 1000,
					'type'    => 'NUMERIC',
					'compare' => '>=',
				],
			] );
		}
	}

	/**
	 * Add difference
	 *
	 * @param array $posts
	 * @param \WP_Query $wp_query
	 *
	 * @return array
	 */
	public function the_posts( array $posts, \WP_Query $wp_query ) {
		if ( ! $this->is_valid_query( $wp_query ) ) {
			return $posts;
		}
		// 1件目のPVより多い投稿の数を返す
		$current_pv = get_post_meta( $posts[0]->ID, '_current_pv', true );
		$query_args = array_merge( $wp_query->query_vars, [
			'ranking'        => '',
			'posts_per_page' => 1,
			'meta_query'     => [
				[
					'key'     => '_current_pv',
					'value'   => $current_pv,
					'type'    => 'NUMERIC',
					'compare' => '>',
				]
			],
			'orderby'     => 'date',
		] );
		$query = new \WP_Query( $query_args );
		$rank = $query->found_posts;
		foreach ( $posts as &$post ) {
			$post->pv         = (int) get_post_meta( $post->ID, '_current_pv', true );
			if ( $current_pv > $post->pv ) {
				++$rank;
				$current_pv = $post->pv;
			}
			$post->rank       = $rank + 1;
		}
		return $posts;
	}

	/**
	 * Detect if query var is valid
	 *
	 * @param \WP_Query $wp_query
	 *
	 * @return bool
	 */
	protected function is_valid_query( \WP_Query $wp_query ) {
		if ( 'best' !== $wp_query->get( 'ranking' ) ) {
			return false;
		}
		// 1ページに10件で、10ページ超えてたらだめ
		$paged = (int) $wp_query->get( 'paged' );
		if ( 10 < $paged ) {
			return false;
		}
		return true;
	}

	/**
	 * @param $title
	 * @param $sep
	 * @param $sep_location
	 * @return string
	 */
	public function wp_title( $title, $sep, $sep_location ) {
		$titles = [ '歴代ベスト' ];
		if ( is_category() ) {
			$titles [] = sprintf( '%s部門', get_queried_object()->name );
		}
		$paged = (int) get_query_var( 'paged' );
		if ( 2 <= $paged ) {
			$titles[] = sprintf( '%d位〜', ( $paged - 1 ) * 10 + 1 );
		}
		$titles [] = get_bloginfo( 'name' );
		return implode( ' ' . $sep . ' ', $titles );
	}
}
