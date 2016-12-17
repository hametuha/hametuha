<?php

if ( ! isset( $content_width ) ) {
	$content_width = 878;
}
/**
 * 現在のテーマのバージョンを返す
 *
 * @return bool|string
 */
function hametuha_version() {
	$theme = wp_get_theme();

	return $theme->get( 'Version' );
}

/**
 * Version Number for Hametuha Theme
 *
 * @deprecated
 */
define( 'HAMETUHA_THEME_VERSION', hametuha_version() );

/**
 * Bootstrap for theme
 */

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
	// Load WPametu
	WPametu::entry( 'Hametuha', __DIR__ . '/src' );
}

/**
 * Allowed sites for API callback
 */
add_filter( 'http_request_host_is_external', function ( $allow, $host, $url ) {
	return false !== array_search($host, [
		'local.hametuha.top',
		'hametuha.pics',
	    'local.hametuha.pics',
	]);
}, 10, 3 );


$oauth_mo = WP_LANG_DIR . "/oauth-server-$locale.mo";
if ( file_exists( $oauth_mo ) ) {
	load_textdomain( 'default', $oauth_mo );
}


/**
 * 読み込むべきスクリプトのフラグ
 * @var array
 */
$script_flg = array();

// フックディレクトリを全部読み込み
foreach ( scandir( __DIR__.'/hooks' ) as $file ) {
	if ( preg_match( '#^[^\.].*\.php$#', $file ) ) {
		require __DIR__.'/hooks/'.$file;
	}
}

// Assets
get_template_part( 'functions/assets' );
get_template_part( 'functions/assets', 'ssl' );
get_template_part( 'functions/assets', 'analytics' );
get_template_part( 'functions/assets', 'cdn' );
get_template_part( 'functions/assets', 'eyecatch' );
get_template_part( 'functions/assets', 'tinymce' );
// 掲示板
get_template_part( 'functions/bulletin-board' );
// キャンペーン
get_template_part( 'functions/campaign' );
// 表示
get_template_part( 'functions/display' );
// amazon
get_template_part( 'functions/hamazon' );
// メール
get_template_part( 'functions/mail' );
// メニュー
get_template_part( 'functions/menu' );
// メタ情報
get_template_part( 'functions/meta' );
// 上書き処理
get_template_part( 'functions/override' );
get_template_part( 'functions/override', 'dashboard' );
get_template_part( 'functions/override', 'error' );
get_template_part( 'functions/override', 'feed' );
get_template_part( 'functions/override', 'lwp' );
get_template_part( 'functions/override', 'amp' );
// 投稿リスト
get_template_part( 'functions/post_list' );
get_template_part( 'functions/post_list', 'admin' );
// 投稿タイプ
get_template_part( 'functions/post_type' );
get_template_part( 'functions/post_type', 'news' );
get_template_part( 'functions/post_type', 'series' );
// ランキング
get_template_part( 'functions/ranking' );
// ショートコード
get_template_part( 'functions/shortcodes', 'page' );
// Social
get_template_part( 'functions/social' );
get_template_part( 'functions/social', 'share' );
// User
get_template_part( 'functions/user' );
get_template_part( 'functions/user', 'caps' );
get_template_part( 'functions/user', 'affiliate' );
get_template_part( 'functions/user', 'picture' );
get_template_part( 'functions/user', 'secret' );
// ユーティリティ
get_template_part( 'functions/utility' );
// ウィジェット
get_template_part( 'functions/widget' );
