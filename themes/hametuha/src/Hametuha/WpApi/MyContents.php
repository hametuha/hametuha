<?php

namespace Hametuha\WpApi;


use Hametuha\WpApi\Pattern\MyContentPattern;

/**
 * My contents
 *
 * @package hametuha
 */
class MyContents extends MyContentPattern {

	/**
	 * {@inheritDoc}
	 */
	protected function get_route() {
		return 'mine/(?P<post_type>[^/]+)';
	}

	/**
	 * Allowed post types.
	 *
	 * @return string[]
	 */
	protected function allowed_types() {
		return [ 'post', 'series', 'list', 'review', 'comment' ];
	}

	/**
	 * Handle GET request.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function handle_get( \WP_REST_Request $request ) {
		$post_type = $request->get_param( 'post_type' );
		$paged     = (int) $request->get_param( 'paged' );
		switch ( $post_type ) {
			case 'post':
			case 'series':
				$args = [
					'post_type'           => $post_type,
					'post_status'         => [ 'publish', 'pending', 'draft', 'future', 'private' ],
					'author'              => get_current_user_id(),
					'posts_per_page'      => 20,
					'paged'               => $paged,
					'ignore_sticky_posts' => true,
					'orderby'             => [ 'date' => 'DESC' ],
				];
				$s    = $request->get_param( 's' );
				if ( $s ) {
					$args['s'] = $s;
				}
				$query = new \WP_Query( $args );
				return new \WP_REST_Response( [
					'posts'       => array_map( [ $this, 'convert_response' ], $query->posts ),
					'found_posts' => $query->found_posts,
					'total'       => $query->max_num_pages,
					'current'     => $paged,
				] );
			case 'list':
				$result = $this->lists->search_list( [
					'paged'          => $paged,
					'posts_per_page' => 20,
					'author'         => get_current_user_id(),
				] );
				return new \WP_REST_Response( [
					'posts'       => array_map( [ $this, 'convert_response' ], $result['posts'] ),
					'found_posts' => $result['found_posts'],
					'total'       => $result['total'],
					'current'     => $result['current'],
				] );
			case 'comment':
				$result = $this->reviews->get_author_comments( get_current_user_id(), [
					'paged'          => $paged,
					'posts_per_page' => 20,
					's'              => $request->get_param( 's' ),
				] );
				return new \WP_REST_Response( [
					'posts'       => array_map( [ $this, 'convert_response' ], $result['comments'] ),
					'found_posts' => $result['found'],
					'total'       => $result['total'],
					'current'     => $result['current'],
				] );
			case 'review':
				$result = $this->rating->get_my_reviewed_posts( get_current_user_id(), [
					'paged'          => $paged,
					'posts_per_page' => 20,
				] );
				return new \WP_REST_Response( [
					'posts'       => array_map( [ $this, 'convert_response' ], $result['reviews'] ),
					'found_posts' => $result['found'],
					'total'       => $result['total'],
					'current'     => $result['current'],
				] );
				break;
			default:
				return new \WP_Error( 'invalid_post_type', __( '投稿タイプが不正です。', 'hametuha' ), [
					'status' => 400,
				] );
		}
	}
}
