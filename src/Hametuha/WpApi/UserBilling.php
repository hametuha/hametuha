<?php

namespace Hametuha\WpApi;


use WPametu\API\Rest\WpApi;

/**
 * User billing information.
 *
 * @package Hametuha\WpApi
 */
class UserBilling extends WpApi {

	/**
	 * Should return route
	 *
	 * @return string
	 */
	protected function get_route() {
		return 'user/billing/(?P<type>[^/]+)';
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
			'type' => [
				'required' => true,
				'validate_callback' => function ( $var ) {
					return in_array( $var, [ 'bank', 'address' ] ) ?: new \WP_Error( 'malformat', sprintf( '%sは更新できません。', $var ) );
				},
			],
		];
		switch ( $method) {
			case 'GET':
				$args['user_id'] = [
					'required' => false,
					'validate_callback' => function ( $var ) {
						return preg_match( '#^(\d+|me)$#', $var ) ?: new \WP_Error( 'malformat', 'user_idはIDまたはmeのみ指定できます。' );
					},
					'default' => 'me',
				];
				break;
			case 'POST':
				foreach ( [
					'_bank' => hametuha_bank_account(),
					'_billing' => hametuha_billing_address()
				  ] as $prefix => $methods ) {
					foreach ( $methods as $key => $val ) {
						$arg = [
							'required' => false,
						];
						$args[ $prefix . '_' . $key ] = $arg;
					}
				}
				break;
		}
		return $args;
	}

	/**
	 * 支払い情報のステータスを取得する
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function handle_get( $request ) {
		$user_id = $request->get_param( 'user_id' );
		if ( 'me' === $user_id ) {
			$user_id = get_current_user_id();
		}
		$ready = false;
		$data  = [];
		switch ( $request->get_param( 'type' ) ) {
			case 'bank':
				$ready = hametuha_bank_ready( $user_id );
				$data  = hametuha_bank_account( $user_id );
				return new \WP_REST_Response( [
					'success' => $ready,
					'message' => $ready ? '振込先情報は入力済みです。' : '振込先情報に不備があります。',
				] );
				break;
			case 'address':
				$ready = hametuha_billing_ready( $user_id );
				$data = hametuha_billing_address( $user_id );
				break;
		}
		return new \WP_REST_Response( [
			'success' => $ready,
			'message' => $ready ? '支払い情報は入力済みです。' : '振込先情報に不備があります。',
			'data'    => $data,
		] );
	}

	/**
	 * 支払い情報を保存する
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function handle_post( $request ) {
		switch ( $request->get_param( 'type' ) ) {
			case 'bank':
				$methods = hametuha_bank_account();
				$prefix = '_bank';
				break;
			case 'address':
				$methods = hametuha_billing_address();
				$prefix = '_billing';
				break;
			default:
				// Do nothing.
				return [];
				break;
		}
		foreach ( $methods as $key => $val ) {
			$k = "{$prefix}_{$key}";
			update_user_meta( get_current_user_id(), $k, $request[ $k ] );
		}
		return new \WP_REST_Response( [
			'success' => true,
			'message' => 'お支払い情報を更新しました。',
		] );
	}

	/**
	 * Parse permission
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return bool
	 */
	public function permission_callback( $request ) {
		if ( 'GET' === $request->get_method() ) {
			$cap = ( 'me' == $request->get_param( 'user_id' ) ) ? 'read' : 'list_users';
			return current_user_can( $cap );
		} else {
			return current_user_can( 'read' );
		}
	}


}
