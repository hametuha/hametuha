<?php

namespace Hametuha\QueryHighJack;


use WPametu\API\QueryHighJack;

class BestQuery extends QueryHighJack {

	protected $query_var = [ 'ranking' ];

	protected $rewrites = [
		'best/page/([0-9]+)/?$' => 'index.php?ranking=best&paged=$matches[1]',
		'best/?$' => 'index.php?ranking=best',
		'best/([^/]+)/page/([0-9]+)/?$' => 'index.php?ranking=best&category_name=$matches[1]&paged=$matches[2]',
		'best/([^/]+)/?$' => 'index.php?ranking=best&category_name=$matches[1]',
	];

	/**
	 * Add meta_query to Query Post
	 *
	 * @param \WP_Query $wp_query
	 */
	public function pre_get_posts( \WP_Query &$wp_query ) {
		if ( $this->is_valid_query( $wp_query ) ) {
			$wp_query->set( 'orderby', 'meta_value_num' );
			$wp_query->set( 'order', 'DESC' );
			$wp_query->set( 'meta_key', '_current_pv' );
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
		if ( !$this->is_valid_query( $wp_query ) ) {
			return $posts;
		}
		// Ranking Query
		$rank_query = <<<SQL
			SELECT COUNT(post_id) FROM {$this->db->postmeta}
			WHERE meta_key = '_current_pv'
			  AND CAST(meta_value AS SIGNED) > %d
SQL;
		// PV diff query
		$yesterday = date_i18n( 'Y-m-d', current_time( 'timestamp' ) - 60 * 60 * 24 );
		$object_ids = [];
		foreach ( $posts as $post ) {
			$object_ids[] = $post->ID;
		}
		if ( $object_ids ) {
			$object_ids = implode( ', ', array_map( 'intval', $object_ids ) );
			$diff_query = <<<SQL
			SELECT object_id, object_value FROM {$this->db->prefix}wpg_ga_ranking
			WHERE category = 'diff'
			  AND object_id IN ({$object_ids})
			  AND calc_date = '{$yesterday}'
SQL;
			$result = $this->db->get_results( $diff_query );
		} else {
			$result = [];
		}
		$object_values = [];
		foreach ( $result as $row ) {
			$object_values[ $row->object_id ] = $row->object_value;
		}
		foreach ( $posts as &$post ) {
			$post->pv = (int) get_post_meta( $post->ID, '_current_pv', true );
			$post->rank = $this->db->get_var( $this->db->prepare( $rank_query, $post->pv ) ) + 1;
			$post->transition = isset( $object_values[ $post->ID ] ) ? (int) ( 0 < $object_values[ $post->ID ] ) : 0;
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
		return 'best' == $wp_query->get( 'ranking' );
	}


}