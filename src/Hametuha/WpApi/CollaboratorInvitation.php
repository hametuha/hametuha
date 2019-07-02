<?php

namespace Hametuha\WpApi;


use Hametuha\AbstractPatterns\UserConverter;
use Hametuha\Model\Author;
use Hametuha\Model\Collaborators;
use WPametu\API\Rest\WpApi;
/**
 * Class CollaboratorInvitation
 * @package Hametuha\WpApi
 * @property \Hametuha\Model\Collaborators $collaborators
 * @property Author $authors
 */
class CollaboratorInvitation extends WpApi {

	use UserConverter;

	protected $models = [
		'collaborators' => Collaborators::class,
		'authors'       => Author::class,
	];

	/**
	 * Should return route
	 *
	 * @return string
	 */
	protected function get_route() {
		return '/collaborators/invitations/(?P<user_id>me|\d+)?';
	}

	/**
	 * Get arguments.
	 *
	 * @param string $method
	 * @return array
	 */
	protected function get_arguments( $method ) {
		$args = [
			'user_id' => [
				'required'          => true,
				'type'              => 'string',
				'validate_callback' => function( $var ) {
					if ( 'me' === $var ) {
						return true;
					} else if ( ! is_numeric( $var ) ) {
						return false;
					} else {
						return (bool) get_userdata( $var );
					}
				},
			],
		];
		switch ( $method ) {
			case 'GET':
				$args = array_merge( $args, [
					'paged' => [
						'type' => 'integer',
						'default' => 1,
						'sanitize_callback' => function( $var ) {
							return max( 1, (int) $var );
						},
					],
				] );
				break;
			case 'POST':
			case 'DELETE':
			$args = array_merge( $args, [
				'series_id' => [
					'type' => 'integer',
					'required' => true,
					'validate_callback' => function ( $var ) {
						return ( $post = get_post( $var ) ) && 'series' === $post->post_type;
					},
				],
			] );
				break;
		}
		return $args;
	}

	/**
	 * Handle GET request to get all invitations.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function handle_get( $request ) {
		$user_id    = $this->get_user( $request );
		$per_page   = 20;
		$cur_page   = $request->get_param( 'paged' );
		$response   = new \WP_REST_Response( array_map( [ $this, 'to_array' ], $this->collaborators->get_invitations( $user_id, $cur_page, $per_page ) ) );
		$total      = $this->collaborators->total_invitations( $user_id );
		$total_page = ceil( $total / $per_page );
		$response->set_headers( [
			'X-WP-Total'        => $total,
			'X-WP-Total-Pages'  => $total_page,
			'X-WP-Current-Page' => $cur_page,
		] );
		return $response;
	}

	/**
	 * Confirm an invitation.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_Error|\WP_REST_Response
	 * @throws \Exception
	 */
	public function handle_post( $request ) {
		$collaborator = $this->get_invitation( $request );
		$series_id = $request->get_param( 'series_id' );
		if ( 0 <= $collaborator->ratio ) {
			return new \WP_Error( 'already_collaborator', 'すでにこの作品へは招待されています。', [
				'status' => 403,
			] );
		}
		$result = $this->collaborators->confirm_invitation( $series_id, $collaborator->ID );
		if ( is_wp_error( $result ) ) {
			return $result;
		} elseif( ! $result ) {
			return new \WP_Error( 'failed_update', 'リクエストを処理できませんでした。やりなおしてください。', [
				'status' => 500,
			] );
		} else {
			/**
			 * Executed when collaborators are approved.
			 */
			do_action( 'hametuha_collaborators_approved', $collaborator, $series_id );
			return new \WP_REST_Response( [
				'success' => true,
				'message' => '作品集への招待を承認しました。',
			] );
		}
	}

	/**
	 * Delete all invitations.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_Error|\WP_REST_Response
	 * @throws \Exception
	 */
	public function handle_delete( $request ) {
		$collaborator = $this->get_invitation( $request );
		$series = get_post( $request->get_param( 'series_id' ) );
		$result = $this->collaborators->delete_collaborator( $request->get_param( 'series_id' ), $collaborator->ID );
		/**
		 * Executed when user denied collaborator invitation.
		 *
		 * @param \WP_User $collaborator
		 * @param \WP_User $user
		 */
		do_action( 'hametuha_collaborators_denied', $collaborator, $series );
		return is_wp_error( $result ) ? $result : new \WP_REST_Response( [
			'success' => true,
			'message' => '作品集への参加を辞退しました。',
		] );
	}

	/**
	 * Get invitation object and if not found, throws exception.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_User
	 * @throws \Exception
	 */
	private function get_invitation( $request ) {
		$user = $this->get_user( $request );
		$collaborator = $this->collaborators->collaborator( $request->get_param( 'series_id' ), $user );
		if ( $collaborator ) {
			return $collaborator;
		} else {
			throw new \Exception( '該当する招待が見つけられませんでした。もしかしたら、取り消されたのかもしれません。', 404 );
		}
	}

	/**
	 * Get user ID.
	 *
	 * @param \WP_REST_Request $request
	 * @return int
	 */
	private function get_user( $request ) {
		$user_id = $request->get_param( 'user_id' );
		return 'me' === $user_id ? get_current_user_id() : (int) $user_id;
	}

	/**
	 * Parse permission
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return bool
	 */
	public function permission_callback( $request ) {
		if ( ! current_user_can( 'read' ) ) {
			return false;
		}
		if ( 'me' !== $request->get_param( 'user_id' ) && ! current_user_can( 'edit_users' ) ) {
			return false;
		}
		return true;
	}
}
