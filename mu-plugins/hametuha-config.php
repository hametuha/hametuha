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
 * wp-config-local.php で HAMETUHA_LOGGED_IN_AS 定数を設定すると、
 * すべてのリクエストで指定されたユーザーとして自動ログイン。
 *
 * 設定例: define( 'HAMETUHA_LOGGED_IN_AS', 'user_login' );
 *
 * ローカル環境でのみ動作します。
 * Chrome DevTools MCPのようなセッション/クッキーを保持できない環境でのテスト用。
 */
add_filter( 'determine_current_user', function( $user_id ) {
	if ( 'local' !== wp_get_environment_type() ) {
		return $user_id;
	}

	// HAMETUHA_LOGGED_IN_AS 定数から対象ロールを取得
	if ( ! defined( 'HAMETUHA_LOGGED_IN_AS' ) || empty( HAMETUHA_LOGGED_IN_AS ) ) {
		return $user_id;
	}

	// ユーザーを取得
	$user = get_user_by( 'login', HAMETUHA_LOGGED_IN_AS );
	if ( ! $user ) {
		return $user_id;
	}

	// 既に同じユーザーIDが設定されている場合は何もしない
	if ( $user_id && (int) $user_id === (int) $user->ID ) {
		return $user_id;
	}

	// 新しいユーザーでログイン
	wp_set_current_user( $user->ID, $user->user_login );
	wp_set_auth_cookie( $user->ID, true );
	return $user->ID;
}, 30 );

/**
 * ローカル環境用：auth_redirect() のオーバーライド
 *
 * Chrome DevTools MCPのようにクッキーを保持できない環境で、
 * wp-adminにアクセスできるようにする。
 *
 * ローカル環境でのみこの簡易版を使用し、本番環境ではWordPressコアの実装を使用。
 */
if ( ! function_exists( 'auth_redirect' ) && 'local' === wp_get_environment_type() ) {
	function auth_redirect() {
		// ログイン済みなら認証OK
		if ( get_current_user_id() ) {
			return;
		}

		// 未ログインならログインページへリダイレクト
		nocache_headers();

		$redirect = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
		$login_url = wp_login_url( $redirect, true );

		wp_redirect( $login_url );
		exit;
	}
}

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
