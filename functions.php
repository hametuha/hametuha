<?php

if ( ! isset( $content_width ) ) {
	$content_width = 970;
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
	// Activate hashboard
	Hametuha\Hashboard::get_instance();
	if ( class_exists( 'Hametuha\\Hashboard\\Router\\Profile' ) ) {
		\Hametuha\Hashboard\Router\Profile::get_instance();
	}
	// Activate sharee
	\Hametuha\Sharee::get_instance();
	// Register all transaction emails.
	if ( class_exists( 'Hametuha\\Hamail\\Pattern\\TransactionalEmail' ) ) {
		foreach ( scandir( __DIR__ . '/src/Hametuha/Notifications/Emails' ) as $file ) {
			if ( ! preg_match( '/^(.*)\.php$/u', $file, $match ) ) {
				continue;
			}
			$class_name = 'Hametuha\\Notifications\\Emails\\' . $match[1];
			if ( ! class_exists( $class_name ) ) {
				continue;
			}
			$class_name::register();
		}
	}
	// Register recaptcha
	if ( class_exists( 'Hametuha\Service\RecaptchaV3' ) ) {
		\Hametuha\Service\RecaptchaV3::get_instance();
	}
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

// Assets
get_template_part( 'functions/assets' );
get_template_part( 'functions/assets', 'ssl' );
get_template_part( 'functions/assets', 'analytics' );
get_template_part( 'functions/assets', 'eyecatch' );
get_template_part( 'functions/assets', 'tinymce' );
// キャンペーン
get_template_part( 'functions/campaign' );
get_template_part( 'functions/calculate' );
// 表示
include __DIR__ . '/functions/display.php';
// amazon
get_template_part( 'functions/hamazon' );
// 暗号化
get_template_part( 'functions/crypt' );
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
// 投稿リスト
get_template_part( 'functions/post_list' );
get_template_part( 'functions/post_list', 'admin' );
// 投稿タイプ
get_template_part( 'functions/post_type' );
get_template_part( 'functions/post_type', 'news' );
get_template_part( 'functions/post_type', 'series' );
// ランキング
get_template_part( 'functions/ranking' );
// Social
get_template_part( 'functions/social' );
// User
get_template_part( 'functions/user' );
include  __DIR__ . '/functions/user-anonymous.php';
get_template_part( 'functions/user', 'affiliate' );
get_template_part( 'functions/user', 'picture' );
get_template_part( 'functions/user', 'secret' );
get_template_part( 'functions/terms' );
// ユーティリティ
get_template_part( 'functions/utility' );
// ウィジェット
get_template_part( 'functions/widget' );

// ディレクトリを全部読み込み
foreach ( [ 'hooks' ] as $folder ) {
	$dir = __DIR__ . '/' . $folder;
	if ( is_dir( $dir ) ) {
		foreach ( scandir( $dir ) as $file ) {
			if ( preg_match( '#^[^\._].*\.php$#', $file ) ) {
				require $dir . '/' . $file;
			}
		}
	}
}
