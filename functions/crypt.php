<?php
/**
 * Crypt related functions
 *
 * @package hametuha
 */

/**
 * Generate unique length
 *
 * @param int $length Byte length.
 *
 * @return string
 */
function hametuha_unique_id( $length ) {
	if ( function_exists( 'random_bytes' ) ) {
		$bytes = random_bytes( $length );
	} else {
		$bytes = openssl_random_pseudo_bytes( $length );
	}
	return bin2hex( $bytes );
}

/**
 * Validate token
 *
 * @param string $slug
 * @param string $token
 *
 * @return bool
 */
function hametuha_validate_web_hook( $slug, $token ) {
	return (bool) get_posts( [
		'post_type'   => 'web-hook',
	    'post_status' => 'publish',
	    'posts_per_page' => 1,
	    'name'        => $slug,
	    'meta_query' => [
	    	[
	    		'key' => '_webhook_token',
		        'value' => $token,
		    ],
	    ],
	] );
}

/**
 * Get rest endpoint URL
 *
 * @param int|WP_Post|null $post
 *
 * @return string
 */
function hametuha_webhook_url( $post ) {
	$post = get_post( $post );
	if ( ! $token = get_post_meta( $post->ID, '_webhook_token', true ) ) {
		return '';
	}
	return home_url( sprintf( '/webhook/do/%s/%s/', $post->post_name, $token ) );
}

/**
 * Check nonce
 *
 * @param string $action
 * @param string $key
 *
 * @return bool
 */
function hametuha_check_nonce( $action, $key = '_wpnonce' ) {
	return isset( $_REQUEST[ $key ] ) && wp_verify_nonce( $_REQUEST[ $key ], $action );
}
