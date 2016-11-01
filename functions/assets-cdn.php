<?php
/**
 * CloudFlare related functions
 *
 * @package hametuha
 *
 */

/**
 * Check if CloudFlare is available.
 *
 * @return bool
 */
function cf_available() {
	return ( defined( 'CF_MAIL' ) && defined( 'CF_TOKEN' ) && defined( 'CF_ZONE_ID' ) ) ;
}

/**
 * Send request to CloudFlare API
 *
 * @param string $endpoint
 * @param array $params
 * @param string $method
 *
 * @return array|mixed|object|WP_Error
 */
function cf_make_request( $endpoint, $params = [], $method = 'GET' ) {
	if ( ! cf_available() ) {
		return new WP_Error( 500, 'No Credentials set.' );
	}
	$endpoint = 'https://api.cloudflare.com/client/v4/' . ltrim( $endpoint, '/' );
	$opts     = [
		CURLOPT_POST           => 'POST' === $method,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_HTTPHEADER     => [
			'X-Auth-Email: ' . CF_MAIL,
			'X-Auth-Key: ' . CF_TOKEN,
			'Content-Type: application/json',
		],
	];
	$method   = strtoupper( $method );
	switch ( $method ) {
		case 'GET':
			if ( $params ) {
				$endpoint .= '?';
				foreach ( $params as $key => $val ) {
					$endpoint .= $key . '&' . rawurldecode( $val );
				}
			}
			break;
		case 'POST':
			$opts[ CURLOPT_POST ]       = true;
			$opts[ CURLOPT_POSTFIELDS ] = json_encode( $params );
			break;
		case 'PUT':
		case 'DELETE':
			$opts[ CURLOPT_CUSTOMREQUEST ] = $method;
			$opts[ CURLOPT_POSTFIELDS ]    = json_encode( $params );
			break;
	}
	$ch = curl_init( $endpoint );
	curl_setopt_array( $ch, $opts );
	if ( WP_DEBUG ) {
		error_log( var_export( $opts, true ) );
	}

	$result = curl_exec( $ch );
	if ( ! $result ) {
		$error = new WP_Error( curl_errno( $ch ), curl_error( $ch ) );
		curl_close( $ch );

		return $error;
	} else {
		curl_close( $ch );
		$response = json_decode( $result );
		if ( is_null( $response ) ) {
			return new WP_Error( 500, 'Failed to parse JSON.' );
		} else {
			if ( WP_DEBUG ) {
				error_log( $result );
			}

			return $response;
		}
	}
}

/**
 * Purge Cache
 *
 * @param array $urls
 * @return array|mixed|object|WP_Error
 */
function cf_purge_cache( $urls ) {
	if ( ! cf_available() ) {
		return new WP_Error( 500, 'CloudFlare is not available' );
	}
	$response = cf_make_request( '/zones/' . CF_ZONE_ID . '/purge_cache', [ 'files' => $urls ], 'DELETE' );
	if ( is_wp_error( $response ) ) {
		return $response;
	} else {
		return $response;
	}
}
