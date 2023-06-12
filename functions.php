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
 * Register i18n.
 */
add_action( 'after_setup_theme', function() {
	load_theme_textdomain( 'hametuha', get_template_directory() . '/languages' );
} );

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
 *
 * @todo Remove absence of hametuha.pics
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
include __DIR__ . '/functions/assets.php';
include __DIR__ . '/functions/assets-ssl.php';
include __DIR__ . '/functions/assets-analytics.php';
include __DIR__ . '/functions/assets-eyecatch.php';
include __DIR__ . '/functions/assets-tinymce.php';
// キャンペーン
include __DIR__ . '/functions/campaign.php';
include __DIR__ . '/functions/calculate.php';
// 表示
include __DIR__ . '/functions/display.php';
include __DIR__ . '/functions/external.php';
// amazon
include __DIR__ . '/functions/hamazon.php';
// 暗号化
include __DIR__ . '/functions/crypt.php';
// メタ情報
include __DIR__ . '/functions/meta.php';
// 上書き処理
include __DIR__ . '/functions/override.php';
include __DIR__ . '/functions/override-dashboard.php';
include __DIR__ . '/functions/override-error.php';
include __DIR__ . '/functions/override-feed.php';
include __DIR__ . '/functions/override-lwp.php';
// 投稿リスト
include __DIR__ . '/functions/post_list.php';
include __DIR__ . '/functions/post_list-admin.php';
// 投稿タイプ
include __DIR__ . '/functions/post_type.php';
include __DIR__ . '/functions/post_type-news.php';
include __DIR__ . '/functions/post_type-series.php';
// ランキング
include __DIR__ . '/functions/ranking.php';
// Social
include __DIR__ . '/functions/social.php';
// User
include __DIR__ . '/functions/user.php';
include  __DIR__ . '/functions/user-anonymous.php';
include __DIR__ . '/functions/user-affiliate.php';
include __DIR__ . '/functions/user-picture.php';
include __DIR__ . '/functions/user-secret.php';
include __DIR__ . '/functions/terms.php';
// ユーティリティ
include __DIR__ . '/functions/utility.php';

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
