<?php

namespace Hametuha\WpApi;


use Hametuha\AbstractPatterns\UserConverter;
use Hametuha\Model\Author;
use Hametuha\Notifications\Emails\CollaboratorMarginUpdate;
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
				'type'              => 'integer',
				'required'          => true,
				'validate_callback' => function( $var ) {
					$post = get_post( $var );
					return $post && 'series' === $post->post_type;
				},
			],
		];
		if ( in_array( $method, [ 'PUT', 'DELETE' ] ) ) {
			$args['collaborator_id'] = [
				'collaborator_id' => [
					'required'          => true,
					'type'              => 'integer',
					'description'       => 'ID of user who is added as collaborator.',
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
						'default'           => true,
						'type'              => 'boolean',
						'sanitize_callback' => function( $var ) {
							return (bool) $var;
						},
					],
				] );
			case 'POST':
				return array_merge( $args, [
					'collaborator' => [
						'required'          => true,
						'type'              => 'string',
						'description'       => 'User nice name',
						'validate_callback' => function( $var ) {
							return ! empty( $var ) && $this->authors->get_by_nice_name( $var );
						},
					],
					'margin'       => [
						'required'          => true,
						'type'              => 'integer',
						'description'       => 'Margin for user. Should be more than 0',
						'validate_callback' => function( $var ) {
							return is_numeric( $var ) && ( 0 < $var ) && ( 0 < 100 );
						},
					],
				] );
			case 'PUT':
				return array_merge( $args, [
					'margin' => [
						'required'          => true,
						'type'              => 'integer',
						'description'       => 'Revenue margin in percentile.',
						'validate_callback' => function( $var ) {
							return is_numeric( $var ) && ( 100 >= $var && 0 <= $var );
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
		$series_id       = $request->get_param( 'series_id' );
		$collaborators   = $this->collaborators->get_collaborators( $series_id );
		$include_waiting = $request->get_param( 'include_waiting' ) && current_user_can( 'edit_post', $series_id );
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
		$margin = $request->get_param( 'margin' );
		// TODO: allow type to be changed.
		$result = $this->collaborators->add_collaborator( $request->get_param( 'series_id' ), $author->ID, 'writer', $margin / 100 * -1 );
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		return new \WP_REST_Response( $this->to_array( $result ) );
	}

	/**
	 * Update margin.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function handle_put( $request ) {
		$margin       = $request->get_param( 'margin' );
		$collaborator = get_userdata( $request->get_param( 'collaborator_id' ) );
		$series_id    = $request->get_param( 'series_id' );
		$response     = $this->collaborators->update_margin( $series_id, $collaborator->ID, $margin );
		if ( is_wp_error( $response ) ) {
			return $response;
		}
		/**
		 * Executed when collaborator setting is updated.
		 *
		 * @param \WP_User $collaborator
		 * @param int      $series_id
		 * @param int      $margin
		 */
		do_action( 'hametuha_collaborators_updated', $collaborator, $series_id, $margin );
		return new \WP_REST_Response( [
			'success' => true,
			'message' => sprintf( '%sさんの報酬を%d%%に変更しました。', $collaborator->display_name, $margin ),
		] );
	}

	/**
	 * Delete collaborator.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function handle_delete( \WP_REST_Request $request ) {
		$user_id   = $request->get_param( 'collaborator_id' );
		$series_id = $request->get_param( 'series_id' );
		$result    = $this->collaborators->delete_collaborator( $series_id, $user_id );
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		/**
		 * Executed when collaborator is deleted.
		 *
		 * @param int $user_id
		 * @param int $series_id
		 */
		do_action( 'hametuha_collaborators_deleted', $user_id, $series_id );
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
