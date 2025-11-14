<?php

namespace Hametuha\WpApi;


use Hametuha\WpApi\Pattern\MyContentPattern;

/**
 * My reading history.
 */
class MyReading extends MyContentPattern {

	/**
	 * {@inheritDoc}
	 */
	protected function allowed_types() {
		return [ 'comment', 'review' ];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_route() {
		return 'reading/(?P<post_type>[^/]+)/?$';
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
			case 'comment':
				$comment_query = new \WP_Comment_Query( [
					'no_found_rows' => false,
					'post_type'     => 'post',
					'paged'         => $paged,
					'number'        => 20,
					'search'        => $request->get_param( 's' ),
					'user_id'       => get_current_user_id(),
				] );
				$total         = $comment_query->found_comments;
				return new \WP_REST_Response( [
					'posts'       => array_map( function ( $comment ) {
						return $this->convert_response( $comment, 'reader' );
					}, $comment_query->get_comments() ),
					'found_posts' => $total,
					'total'       => ceil( $total / 20 ),
					'current'     => $paged,
				] );
			case 'review':
				$result = $this->rating->get_reviewed_posts_by( get_current_user_id(), [
					'paged'          => $paged,
					'posts_per_page' => 20,
				] );
				return new \WP_REST_Response( [
					'posts'       => array_map( function ( $review ) {
						return $this->convert_response( $review, 'reader' );
					}, $result['reviews'] ),
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
