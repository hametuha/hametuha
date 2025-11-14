<?php

namespace Hametuha\WpApi;


use Hametuha\Model\Sales;
use WPametu\API\Rest\WpApi;

/**
 * User reward API
 *
 * @property Sales $sales
 * @package Hametuha\WpApi
 */
class UserSales extends WpApi {

	protected $models = [
		'sales' => Sales::class,
	];

	/**
	 * Should return route
	 *
	 * @return string
	 */
	protected function get_route() {
		return '/sales/history/(?P<user_id>total|me|\d+)';
	}

	/**
	 * Get arguments
	 *
	 * @param string $method
	 * @return array
	 */
	public function get_arguments( $method ) {
		$args = [
			'user_id' => [
				'required'          => true,
				'validate_callback' => function ( $var ) {
					return preg_match( '#^(total|me|\d+)$#', $var ) ?: new \WP_Error( 'malformat', 'ユーザーIDが不正です。' );
				},
			],
		];
		switch ( $method ) {
			case 'GET':
				$args['year']  = [
					'default'           => date_i18n( 'Y' ),
					'validate_callback' => function ( $var ) {
						return preg_match( '#\d{4}#', $var ) ?: new \WP_Error( 'malformat', '年は4桁の整数です。' );
					},
				];
				$args['month'] = [
					'default'           => date_i18n( 'm' ),
					'validate_callback' => function ( $var ) {
						return preg_match( '#\d{1,2}#', $var ) ?: new \WP_Error( 'malformat', '月は2桁の整数です。' );
					},
				];
				break;
			case 'POST':
				break;
		}
		return $args;
	}

	/**
	 * Get sales report
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response
	 */
	public function handle_get( $request ) {
		switch ( $request['user_id'] ) {
			case 'me':
				$user_id = get_current_user_id();
				break;
			case 'total':
				$user_id = 0;
				break;
			default:
				$user_id = $request['user_id'];
				break;
		}
		$response = [
			'total'   => 0,
			'records' => [],
		];
		foreach ( $this->sales->get_records( [
			'author'   => $user_id,
			'per_page' => 0,
			'year'     => $request['year'],
			'month'    => $request['month'],
		] ) as $sales ) {
			$response['total']    += (float) $sales->royalty;
			$response['records'][] = $sales;
		}
		return new \WP_REST_Response( $response );
	}

	/**
	 * @param \WP_REST_Request $request
	 * @return bool
	 */
	public function permission_callback( $request ) {
		if ( 'me' == $request->get_param( 'user_id' ) ) {
			return current_user_can( 'read' );
		} else {
			return current_user_can( 'edit_users' );
		}
	}
}
