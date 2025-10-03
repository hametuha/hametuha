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
 * ローカル環境用：簡易ログイン機能
 *
 * URLパラメータ ?login_as に以下の値を設定すると自動ログイン:
 * - admin: 管理者（takahashi_fumiki）
 * - editor: 編集者（takahashi_fumiki - editorユーザーが存在しないため）
 * - author: 投稿者（@10kgtr）
 * - subscriber: 購読者（@__k__n__c__）
 *
 * 例: https://hametuha.info/ideas/?login_as=admin
 *
 * ローカル環境でのみ動作します。
 */
add_action( 'plugins_loaded', function() {
	if ( 'local' !== wp_get_environment_type() ) {
		return;
	}

	$login_as = filter_input( INPUT_GET, 'login_as' );
	if ( empty( $login_as ) ) {
		return;
	}

	// ロールごとのテストユーザー定義
	$test_users = [
		'admin'      => 'takahashi_fumiki',
		'editor'     => 'takahashi_fumiki', // editorユーザーが存在しないため、adminで代用
		'author'     => '@10kgtr',
		'subscriber' => '@__k__n__c__',
	];

	$username = $test_users[ $login_as ] ?? '';
	if ( empty( $username ) ) {
		return;
	}

	$user = get_user_by( 'login', $username );
	if ( ! $user ) {
		return;
	}

	// 現在のユーザーと同じ場合は何もしない
	if ( is_user_logged_in() && get_current_user_id() === $user->ID ) {
		wp_safe_redirect( remove_query_arg( 'login_as' ) );
		exit;
	}

	// 新しいユーザーでログイン
	wp_clear_auth_cookie();
	wp_set_current_user( $user->ID, $user->user_login );
	wp_set_auth_cookie( $user->ID, true );

	// ?login_as パラメータを削除してリダイレクト（無限ループ防止）
	$redirect_url = remove_query_arg( 'login_as' );
	wp_safe_redirect( $redirect_url );
	exit;
} );

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
