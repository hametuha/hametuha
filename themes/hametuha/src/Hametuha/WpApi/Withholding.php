<?php

namespace Hametuha\WpApi;


use WPametu\API\Rest\WpApi;

/**
 * 源泉徴収のある支払いの一覧を返す API
 *
 * GAS から毎月取得して報酬支払一覧表にマージするために使う。
 * 認証はアプリケーションパスワードを想定。
 *
 * @package Hametuha\WpApi
 */
class Withholding extends WpApi {

	/**
	 * Should return route
	 *
	 * @return string
	 */
	protected function get_route() {
		return '/sales/withholding/(?P<year>\d{4})/(?P<month>\d{1,2})';
	}

	/**
	 * Get arguments
	 *
	 * @param string $method
	 * @return array
	 */
	protected function get_arguments( $method ) {
		return [
			'year'  => [
				'required'          => true,
				'validate_callback' => function ( $var ) {
					return preg_match( '#^\d{4}$#', $var ) ? true : new \WP_Error( 'malformat', '年は4桁の整数です。' );
				},
			],
			'month' => [
				'required'          => true,
				'validate_callback' => function ( $var ) {
					return ( 1 <= (int) $var && 12 >= (int) $var ) ? true : new \WP_Error( 'malformat', '月は1〜12の整数です。' );
				},
			],
		];
	}

	/**
	 * Get withholding records.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response
	 */
	public function handle_get( $request ) {
		$year  = (int) $request->get_param( 'year' );
		$month = (int) $request->get_param( 'month' );
		return new \WP_REST_Response( [
			'year'    => $year,
			'month'   => $month,
			'records' => \Hametuha\Master\Withholding::get_records( $year, $month ),
		] );
	}

	/**
	 * 会計を扱えるユーザーのみ
	 *
	 * @param \WP_REST_Request $request
	 * @return bool
	 */
	public function permission_callback( $request ) {
		return current_user_can( 'edit_users' );
	}
}
