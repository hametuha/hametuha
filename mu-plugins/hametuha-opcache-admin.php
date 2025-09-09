<?php
/**
 * Plugin Name: Hametuha OPCache Admin (MU)
 *
 * Description: PHPのOpCacheを管理するプラグイン
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Do not access this file directly.' );
}

add_action( 'admin_menu', function () {
	add_management_page( 'OPCache', 'OPCache', 'manage_options', 'opcache-admin', function () {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Forbidden' );
		}

		// Reset action (with nonce)
		if ( isset( $_POST[ 'opcache_reset' ] ) && check_admin_referer( 'opcache_reset_nonce' ) ) {
			$ok = function_exists( 'opcache_reset' ) ? opcache_reset() : false;
			echo '<div class="notice notice-' . ( $ok ? 'success' : 'error' ) . '"><p>OPcache reset: ' . ( $ok ? 'OK' : 'FAILED' ) . '</p></div>';
		}

		$status = function_exists( 'opcache_get_status' ) ? opcache_get_status( false ) : null;
		echo '<div class="wrap"><h1>OPCache</h1>';
		if ( ! $status ) {
			echo '<p>OPcache not available.</p></div>';

			return;
		}

		$mem   = $status[ 'memory_usage' ];
		$stats = $status[ 'opcache_statistics' ];
		echo '<table class="widefat" style="max-width:800px"><tbody>';
		printf( '<tr><th>Used memory</th><td>%.1f MB</td></tr>', $mem[ 'used_memory' ] / 1048576 );
		printf( '<tr><th>Free memory</th><td>%.1f MB</td></tr>', $mem[ 'free_memory' ] / 1048576 );
		printf( '<tr><th>Wasted memory</th><td>%.1f MB (%.2f%%)</td></tr>',
			$mem[ 'wasted_memory' ] / 1048576, $mem[ 'current_wasted_percentage' ] );
		printf( '<tr><th>Cached scripts</th><td>%d</td></tr>', $stats[ 'num_cached_scripts' ] );
		printf( '<tr><th>Hits / Misses</th><td>%d / %d</td></tr>', $stats[ 'hits' ], $stats[ 'misses' ] );
		printf( '<tr><th>Hit rate</th><td>%.2f%%</td></tr>', $stats[ 'opcache_hit_rate' ] );
		echo '</tbody></table>';

		echo '<form method="post" style="margin-top:1em">';
		wp_nonce_field( 'opcache_reset_nonce' );
		submit_button( 'OPcache Reset', 'delete', 'opcache_reset', false );
		echo '</form></div>';
	} );
} );
