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

/**
 * wp_dieやphp-error.phpをプレビューする
 *
 * 1. URLパラメータ ?error-preview=wp で wp_die を表示
 * 2. URLパラメータ ?error-preview=php で php-error.php を表示
 */
add_action( 'template_redirect', function () {
	if ( 'local' !== wp_get_environment_type() ) {
		return;
	}
	switch ( filter_input( INPUT_GET, 'error-preview' ) ) {
		case 'wp':
			wp_die( 'これは wp_die のテストです。', 'wp_die テスト', [ 'response' => 403 ] );
		case 'php':
			// 存在しない関数を呼ぶ
			hametuha_non_existing_function_for_error_preview();
	}
} );
