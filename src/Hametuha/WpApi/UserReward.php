<?php

namespace Hametuha\WpApi;


use Hametuha\Model\UserSales;
use WPametu\API\Rest\WpApi;

/**
 * User reward API
 *
 * @property UserSales $sales
 * @package Hametuha\WpApi
 */
class UserReward extends WpApi {

	protected $models = [
		'sales' => UserSales::class,
	];

	/**
	 * Should return route
	 *
	 * @return string
	 */
	protected function get_route() {
		return '/sales/rewards/(?P<user_id>total|me|\d+)';
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
				'required' => true,
				'validate_callback' => function( $var ) {
					return preg_match( '#^(total|me|\d+)$#', $var ) ?: new \WP_Error( 'malformat', 'ユーザーIDが不正です。' );
				}
			],
		];
		switch ( $method ) {
			case 'GET':
				$args['year'] = [
					'default' => date_i18n( 'Y' ),
					'validate_callback' => function( $var ) {
						return preg_match( '#\d{4}#', $var ) ?: new \WP_Error( 'malformat', '年は4桁の整数です。' );
					},
				];
				$args['month'] = [
					'default' => date_i18n( 'm' ),
					'validate_callback' => function( $var ) {
						return preg_match( '#\d{1,2}#', $var ) ?: new \WP_Error( 'malformat', '月は2桁の整数です。' );
					},
				];
				$args['status'] = [
					'default' => 'all',
					'validate_callback' => function( $var ) {
						return in_array( $var, [ 'all', '0', '1' ] ) ?: new \WP_Error( 'malformat', '指定できるステータスはの all, 0, 1 いずれかです。' );
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
		$user_id = 'me' == $request['user_id'] ? get_current_user_id() : $request['user_id'];
		$response = [
			'total'     => 0,
			'deducting' => 0,
			'records'   => [],
		];
		if ( 'all' == $request->get_param( 'status' ) ) {
			$status = [ 0, 1 ];
			$range = true;
		} else {
			$status = $request['status'];
			$range = false;
		}

		foreach ( $this->sales->get_billing_list( $request['year'], $request['month'], $user_id, $status, $range ) as $sales ) {
			$response['total'] += $sales->total;
			$response['deducting'] += $sales->deducting;
			$sales->paid = '0000-00-00 00:00:00' != $sales->fixed;
			$response['records'][] = $sales;
		}
		$response['enough'] = $response['total'] > hametuha_minimum_payment();
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
