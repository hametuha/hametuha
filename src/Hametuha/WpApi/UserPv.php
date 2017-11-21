<?php
namespace Hametuha\WpApi;

/**
 * Show user PV
 *
 * @package hametuha
 */
class UserPv extends AnalyticsPattern {
	/**
	 * Should return route
	 *
	 * @return string
	 */
	protected function get_route() {
		return 'stats/access/(?P<id>\d+|me)';
	}




	/**
	 * Get arguments for method.
	 *
	 * @param string $method 'GET', 'POST', 'PUSH', 'PATCH', 'DELETE', 'HEAD', 'OPTION'
	 *
	 * @return array
	 */
	protected function get_arguments( $method ) {
		return $this->add_date_fields( [
			'id' => [
				'required' => true,
				'validation_callback' => function( $id, $request ) {
					return ( is_numeric( $id ) || ( 'me' === $id )  ) || new \WP_Error( 'malformat', 'ユーザーIDの指定が不正です。' );
				}
			],
		] );
	}

	/**
	 * Handle request.
	 *
	 * @return \WP_REST_Response||\WP_Error
	 */
	public function handle_get() {
		return [
			'success' => true,
		];
	}

	/**
	 * Parse permission
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return bool
	 */
	public function permission_callback( $request ) {
		if ( 'me' === $request->get_param( 'id' ) ) {
			return current_user_can( 'read' );
		} else {
			return current_user_can( 'edit_others_posts' );
		}
	}


}
