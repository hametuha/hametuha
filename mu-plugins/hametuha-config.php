<?php
/**
 * Plugin Name: Hametuha Local Env Config
 * Description: Must-use plugin for Hametuha site configuration for local env
 * Version: 1.0.0
 * Author: Hametuha
 */

// Prevent direct access
if ( ! defined('ABSPATH' ) ) {
    exit;
}

/**
 * ローカル環境の場合は管理バーをカスタマイズ
 */
add_action('admin_bar_menu', function( WP_Admin_Bar $wp_admin_bar ) {
    $wp_admin_bar->add_menu([
        'id'    => 'hametuha-info',
        'title' => '［ローカル環境］',
        'href'  => admin_url(),
    ]);
	$counter = 1;
	foreach ( [
		'phpMyAdmin' => 'http://localhost:8081',
		'Mailpit' => 'http://localhost:8026',
	] as $label => $link ) {
		$wp_admin_bar->add_node( [
			'id' => sprintf( 'hametuha-dev-%d', $counter ),
			'title' => $label,
			'href'  => $link,
			'parent' => 'hametuha-info',
			'meta' => [
				'target' => '_blank'
			],
		] );
		++$counter;
	}
});
