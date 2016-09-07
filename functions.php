<?php


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

get_template_part( 'functions/utility' );
get_template_part( 'functions/display' );
get_template_part( 'functions/cdn' );
get_template_part( 'functions/ranking' );
get_template_part( 'functions/meta' );
get_template_part( 'functions/post_types' );
get_template_part( 'functions/post_list' );
get_template_part( 'functions/post_list_admin' );
get_template_part( 'functions/series' );
get_template_part( 'functions/dashboard' );
get_template_part( 'functions/assets' );
get_template_part( 'functions/assets', 'ssl' );
get_template_part( 'functions/analytics' );
get_template_part( 'functions/override' );
get_template_part( 'functions/social' );
get_template_part( 'functions/social', 'share' );
get_template_part( 'functions/user' );
get_template_part( 'functions/user_content' );
get_template_part( 'functions/user_change_login' );
get_template_part( 'functions/user_profile_picture' );
get_template_part( 'functions/widget' );
get_template_part( 'functions/menu' );
get_template_part( 'functions/error' );
get_template_part( 'functions/tinyMCE' );
get_template_part( 'functions/hamazon' );
get_template_part( 'functions/eyecatch' );
get_template_part( 'functions/bulletin-board' );
get_template_part( 'functions/device' );
get_template_part( 'functions/lwp' );
get_template_part( 'functions/feed' );
get_template_part( 'functions/mail' );
get_template_part( 'functions/campaign' );
get_template_part( 'functions/news' );
get_template_part( 'functions/amp' );
get_template_part( 'functions/affiliate' );
