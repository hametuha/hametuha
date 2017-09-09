<?php

namespace Hametuha\Rest;

use WPametu\API\Rest\RestTemplate;

/**
 * Web hook parser
 *
 * @package hametuha
 * @since 5.4.0
 */
class WebHook extends RestTemplate {

	public static $prefix = 'webhook';

	/**
	 * @param string $slug
	 * @param string $token
	 *
	 * @return array|object
	 * @throws \Exception
	 */
	public function post_do( $slug = '', $token = '' ) {
		if ( ! hametuha_validate_web_hook( $slug, $token ) ) {
			throw new \Exception( 404, '該当するWebフックは存在しません。' );
		}
		try {
			/**
			 * hametuha_webhook_response
			 *
			 * Make response to valid web hook request.
			 *
			 * @param false $request
			 * @param string $slug
			 * @param array $params
			 *
			 * @return \WP_Error|array|object
			 */
			$response = apply_filters( 'hametuha_webhook_response', false, $slug, $_POST );
			if ( false === $response ) {
				throw new \Exception( 'このエンドポイントは実装されていません。', 501 );
			} elseif ( is_wp_error( $response ) ) {
				throw new \Exception( $response->get_error_message(), $response->get_error_code() );
			} elseif ( is_array( $response ) || is_object( $response ) ) {
				wp_send_json_success( $response );
			} else {
				throw new \Exception( 'サーバーのエラーにより不正なレスポンスが返りました。', 500 );
			}
		} catch ( \Exception $e ) {
			header( 'Content-Type: ' . $this->content_type );
			wp_send_json_error( $e->getMessage(), $e->getCode() );
		}
	}
}
