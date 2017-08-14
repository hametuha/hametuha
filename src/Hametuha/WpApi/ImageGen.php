<?php

namespace Hametuha\WpApi;


use Hametuha\Model\Jobs;
use WPametu\API\Rest\WpApi;

/**
 * Image generator
 *
 * @package Hametuha\WpApi
 * @property Jobs $jobs
 */
class ImageGen extends WpApi {

	/**
	 * Get route
	 *
	 * @return string
	 */
	protected function get_route() {
		return 'text/of/(?P<id>\\d+)';
	}

	/**
	 * Parse permission
	 *
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return bool
	 */
	public function permission_callback( $request ) {
		switch ( strtoupper( $request->get_method() ) ) {
			default:
				return current_user_can( 'read' );
				break;
		}
	}


	/**
	 * Get arguments for method.
	 *
	 * @param string $method 'GET', 'POST', 'PUSH', 'PATCH', 'DELETE', 'HEAD', 'OPTION'
	 *
	 * @return array
	 */
	protected function get_arguments( $method ) {
		switch ( $method ) {
			case 'POST':
				return [
					'id' => [
						'required' => true,
						'validate_callback' => function( $var ) {
							return get_post( $var );
						},
					],
					'text' => [
						'required' => true,
						'validate_callback' => function( $var ) {
							return ! empty( $var );
						},
					],
				];
				break;
			default:
				return [];
				break;
		}
	}

	/**
	 * Register image gen
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function handle_post( \WP_REST_Request $request ) {
		if ( ! defined( 'HAMEPIC_URL' ) ) {
			return new \WP_Error( 'no_rest_url', 'はめぴくっ！のURLが設定されていません。', [
				'status' => 500,
			] );
		}
		// JOBを登録
		$job = $this->jobs->add( '' );
		$url = rest_url( '/hametuha/v1/text/of/' . $job->job_id );

		// 結果を取得し、IDを保存
		$endpoint = trailingslashit( HAMEPIC_URL ) . 'camera/ss';
		$result = wp_remote_post( $endpoint, [
			'url' => $url,
		    'size' => '1200x1200',
		    'post_back' => $url,
		] );
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		return new \WP_REST_Response( [
			'success' => true,
		    'message' => 'ご紹介ありがとうございます。作者も喜んでいることでしょう。',
		] );
	}


	/**
	 * Getter
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'jobs':
				return Jobs::get_instance();
				break;
			default:
				return parent::__get( $name );
				break;
		}
	}


}
