<?php

namespace Hametuha\WpApi;


use Hametuha\Sharee\Models\RevenueModel;
use WPametu\API\Rest\WpApi;

/**
 * User reward API
 *
 * @property RevenueModel $sales
 * @package Hametuha\WpApi
 */
class UserPayments extends WpApi {

	protected $models = [
		'sales' => RevenueModel::class,
	];

	/**
	 * Should return route
	 *
	 * @return string
	 */
	protected function get_route() {
		return '/sales/payments/(?P<user_id>all|me|\d+)';
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
				'validate_callback' => function( $var ) {
					return preg_match( '#^(all|me|\d+)$#', $var ) ?: new \WP_Error( 'malformat', 'ユーザーIDが不正です。' );
				},
			],
		];
		switch ( $method ) {
			case 'GET':
				$args['year'] = [
					'default'           => date_i18n( 'Y' ),
					'validate_callback' => function( $var ) {
						return preg_match( '#\d{4}#', $var ) ?: new \WP_Error( 'malformat', '年は4桁の整数です。' );
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
		switch ( $request->get_param( 'user_id' ) ) {
			case 'me':
				$user_id = get_current_user_id();
				break;
			case 'all':
				$user_id = 0;
				break;
			default:
				$user_id = (int) $request->get_param( 'user_id' );
				break;
		}
		$response = [
			'total'     => 0,
			'deducting' => 0,
			'records'   => [],
		];
		foreach ( $this->sales->get_payment_list( $request->get_param( 'year' ), $user_id ) as $record ) {
			$response['total']     += $record->total;
			$response['deducting'] += $record->deducting;
			$record->label          = $this->sales->type_label( $record->revenue_type );
			$response['records'][]  = $record;
		}
		return new \WP_REST_Response( $response );
	}

	/**
	 * @param \WP_REST_Request $request
	 * @return bool
	 */
	public function permission_callback( $request ) {
		if ( 'me' === $request->get_param( 'user_id' ) ) {
			return current_user_can( 'read' );
		} else {
			return current_user_can( 'edit_users' );
		}
	}
}
