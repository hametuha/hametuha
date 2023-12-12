<?php

namespace Hametuha\WpApi;


use Hametuha\WpApi\Pattern\EpubFilePattern;

/**
 * ePub file list api
 *
 * @package hametuha
 */
class EpubFiles extends EpubFilePattern {

	/**
	 * {@inheritDoc}
	 */
	protected function get_route() {
		return 'epub/files/';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_arguments( $method ) {
		switch ( $method ) {
			case 'GET':
				return [
					's' => [
						'type'        => 'string',
						'description' => 'Search Term',
						'default'     => '',
					],
					'p' => [
						'type'        => 'integer',
						'description' => 'Series ID. Needs permission to edit the post.',
						'default'     => 0,
						'validate_callback' => function( $var ) {
							if ( 1 > $var ) {
								return current_user_can( 'edit_others_posts' );
							} else {
								return current_user_can( 'edit_post', $var );
							}
						},
					],
					'author' => [
						'type'        => 'integer',
						'description' => 'Author ID',
						'default'     => 0,
						'validate_callback' => function( $var ) {
							if ( ! $var ) {
								return current_user_can( 'edit_others_posts' );
							} else {
								return current_user_can( 'edit_others_posts' ) || ( $var === get_current_user_id() );
							}
						},
					],
					'posts_per_page' => [
						'type'        => 'integer',
						'description' => 'Number per page',
						'default'     => 10,
						'validate_callback' => function( $var ) {
							return ( 0 < $var ) && ( 200 > $var );
						},
					],
					'paged' => [
						'type'        => 'integer',
						'description' => 'Page number',
						'default'     => 1,
						'sanitize_callback' => function( $var ) {
							return max( 1, (int) $var );
						},
					],
					'order' => [
						'type'        => 'string',
						'description' => 'Order',
						'default'     => 'DESC',
						'enum'        => [ 'ASC', 'DESC' ],
					],
					'orderby' => [
						'type'        => 'string',
						'description' => 'Order',
						'default'     => 'DESC',
						'enum'        => [ 'ASC', 'DESC' ],
					],
				];
			case 'POST':
				return [
					'p' => [
						'required'    => true,
						'type'        => 'integer',
						'description' => 'Post ID',
						'validate_callback' => function( $var ) {
							return ( 'series' === get_post_type( $var ) ) && current_user_can( 'edit_post', $var );
						},
					],
				];
			default:
				return [];
		}
	}

	/**
	 * Handle file list.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function handle_get( $request ) {
		$args = [
			's'      => $request->get_param( 's' ),
			'p'      => $request->get_param( 'p' ),
			'author' => $request->get_param( 'author' ),
			'order'  => $request->get_param( 'order' ),
			'orderby' => $request->get_param( 'orderby' ),
		];
		$files = $this->files->get_files( $args, $request->get_param( 'posts_per_page' ), $request->get_param( 'paged' ) - 1 );
		return new \WP_REST_Response( [
			'total' => $this->files->found_count(),
			'items' => $files,
		] );
	}

	/**
	 * Handle POST request.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function handle_post( $request ) {
		$post = get_post( $request->get_param( 'p' ) );
		// todo: Move Rest/Epub to here.
	}

	/**
	 * Permission check.
	 *
	 * @param \WP_REST_Request $request
	 * @return bool
	 */
	public function permission_callback( $request ) {
		return current_user_can( 'edit_posts' );
	}
}
