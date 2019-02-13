<?php

namespace Hametuha\Service;


use Ramsey\Uuid\Uuid;
use WPametu\Pattern\Singleton;

/**
 * Measurement Protocol Wrapper
 *
 *
 * @see https://developers.google.com/analytics/devguides/collection/protocol/v1/?hl=ja
 * @package hametuha
 */
class AnalyticsMesurementApi extends Singleton {

	private $ua = '';

	private $cid = '';

	private $tmp = '';

	const ENDPOINT = 'https://www.google-analytics.com/collect';

	/**
	 * Constructor
	 *
	 * @param array $setting
	 */
	public function __construct( array $setting = [] ) {
		$setting = wp_parse_args( $setting, [
			'ua'  => '',
			'cid' => '',
		] );
		$this->ua = $setting['ua'];
		$this->cid = $setting['cid'];
		$this->tmp = (string) Uuid::uuid4();
	}

	/**
	 * Send request to GA.
	 *
	 * @param array $param
	 * @return array|\WP_Error
	 */
	public function request( array $param ) {
		$req_param = $this->fill_params( $param );
		$response = wp_remote_post( self::ENDPOINT, [
			'body' => $req_param,
		] );
		return $response;
	}

	/**
	 * Get default param.
	 *
	 * @return array
	 */
	private function default_params() {
		return [
			'v'   => 1,
			'tid' => $this->ua,
			'cid' => $this->get_client_id(),
			'uip' => $this->user_ip(),
		];
	}

	/**
	 * Record Event.
	 *
	 * @param array $params Should have category(ec), action(ea).
	 * @return array|\WP_Error
	 */
	public function event( $params ) {
		$params = $this->fill_params( wp_parse_args( $params, [
			't'  => 'event',
			'ev' => 1,
		] ) );
		return $this->request( $params );
	}
	/**
	 * Fill parameters.
	 *
	 * @param  array $params
	 * @return array
	 */
	public function fill_params( array $params ) {
		$args = [];
		foreach ( array_merge( $this->default_params(), $params ) as $key => $value ) {
			$key = preg_replace( '/dimension(\d+)/u', 'cd$1', $key );
			$args[ $key ] = $value;
		}
		return $args;
	}

	/**
	 * Get available IP.
	 *
	 * @return string
	 */
	public function user_ip() {
		foreach ( [ 'REMOTE_ADDR', 'SERVER_ADDR' ] as $key ) {
			if ( isset( $_SERVER[ $key ] ) && $_SERVER[ $key ] ) {
				return $_SERVER[ $key ];
			}
		}
		return '127.0.0.1';
	}

	/**
	 * Get client id.
	 *
	 * @return string
	 */
	private function get_client_id() {
		if ( $this->cid ) {
			return $this->cid;
		} elseif ( isset( $_COOKIE['_ga'] ) && $_COOKIE['_ga'] ) {
			return preg_replace( '/^GA\d\.\d/u', '', $_COOKIE['_ga'] );
		} else {
			return $this->tmp;
		}
	}
}
