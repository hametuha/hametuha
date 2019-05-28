<?php

namespace Hametuha\WpApi;


use Hametuha\AbstractPatterns\UserConverter;
use Hametuha\Model\Author;
use WPametu\API\Rest\WpApi;

/**
 * Class Collaborators
 * @package hametuha
 * @property \Hametuha\Model\Collaborators $collaborators
 * @property Author $authors
 */
class Collaborators extends WpApi {

	use UserConverter;

	protected $models = [
		'collaborators' => \Hametuha\Model\Collaborators::class,
		'authors'       => Author::class,
	];

	/**
	 * Should return route
	 *
	 * @return string
	 */
	protected function get_route() {
		return '/collaborators/(?P<series_id>\d+)/?';
	}

	/**
	 * Get arguments for method.
	 *
	 * @param string $method 'GET', 'POST', 'PUSH', 'PATCH', 'DELETE', 'HEAD', 'OPTION'
	 *
	 * @return array
	 */
	protected function get_arguments( $method ) {
		$args = [
			'series_id' => [
				'type' => 'integer',
				'required' => true,
				'validate_callback' => function( $var ) {
					$post = get_post( $var );
					return $post && 'series'=== $post->post_type;
				},
			],
		];
		if ( in_array( $method, [ 'PUSH', 'DELETE' ] ) ) {
			$args['collaborator_id'] = [
				'collaborator_id' => [
					'required' => true,
					'type'     => 'integer',
					'description' => 'ID of user who is added as collaborator.',
					'validate_callback' => function( $var, \WP_REST_Request $request ) {
						return is_numeric( $var ) && $this->collaborators->collaborator_exists( $request->get_param( 'series_id' ), $var );
					},
				],
			];
		}
		switch ( $method ) {
			case 'GET':
				return array_merge( $args, [
					'include_waiting' => [
						'default' => true,
						'type' => 'boolean',
						'sanitize_callback' => function( $var ) {
							return (bool) $var;
						},
					],
				] );
			case 'POST':
				return array_merge( $args, [
					'collaborator' => [
						'require'     => true,
						'type'        => 'string',
						'description' => 'User nice name',
						'validate_callback' => function( $var ) {
							return ! empty( $var ) && $this->authors->get_by_nice_name( $var );
						},
					],
				] );
			default:
				return $args;
		}
	}

	/**
	 * Handle GET request.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function handle_get( $request ) {
		$series_id = $request->get_param( 'series_id' );
		$collaborators = $this->collaborators->get_collaborators( $series_id );
		$include_waiting  = $request->get_param( 'include_waiting' ) && current_user_can( 'edit_post', $series_id );
		return new \WP_REST_Response( array_values( array_map( [ $this, 'to_array' ], array_filter( $collaborators, function( $user ) use ( $include_waiting ) {
			return $include_waiting || 0 <= (int) $user->location;
		} ) ) ) );
	}

	/**
	 * Handle POST request.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function handle_post( $request ) {
		$author = $this->authors->get_by_nice_name( $request->get_param( 'collaborator' ) );
		if ( ! $author || ! user_can( $author, 'edit_posts' ) ) {
			return new \WP_Error( 'invalid_author', '指定されたユーザーは存在しないか、破滅派同人ではありません。' );
		}
		$result = $this->collaborators->add_collaborator( $request->get_param( 'series_id' ), $author->ID );
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		return new \WP_REST_Response( $this->to_array( $result ) );
	}

	/**
	 * Delete collaborator.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function handle_delete( \WP_REST_Request $request ) {
		$user_id = $request->get_param( 'collaborator_id' );
		$result = $this->collaborators->delete_collaborator( $request->get_param( 'series_id' ), $user_id );
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		return new \WP_REST_Response( [
			'success' => true,
			'message' => 'ユーザーを関係者から削除しました。',
			'id'      => $user_id,
		] );
	}

	/**
	 * Parse permission
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return bool
	 */
	public function permission_callback( $request ) {
		switch ( $request->get_method() ) {
			case 'GET':
				return true;
			default:
				return current_user_can( 'edit_post', $request->get_param( 'series_id' ) );
		}
	}
}
