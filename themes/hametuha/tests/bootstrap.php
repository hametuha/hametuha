<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Hametuha
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	// Try local wp-tests directory first (recommended), then Docker tmp, then system tmp
	// From themes/hametuha, go up to project root then to wp-tests
	$wp_tests_path = dirname( dirname( dirname( dirname( __DIR__ ) ) ) ) . '/wp-tests';
	if ( file_exists( $wp_tests_path . '/includes/functions.php' ) ) {
		$_tests_dir = $wp_tests_path;
	} elseif ( file_exists( '/tmp/wordpress-tests-lib/includes/functions.php' ) ) {
		$_tests_dir = '/tmp/wordpress-tests-lib';
	} else {
		$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
	}
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find $_tests_dir/includes/functions.php" . PHP_EOL;
	echo "Have you run bin/install-wp-tests.sh or bin/setup-wp-tests-docker.sh ?" . PHP_EOL;
	exit( 1 );
}

// Load PHPUnit Polyfills if available (for newer WordPress versions)
$polyfills_path = dirname( __DIR__ ) . '/vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php';
if ( file_exists( $polyfills_path ) ) {
	require_once $polyfills_path;
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Registers theme
 */
function _register_theme() {

	$theme_dir = dirname( __DIR__ );
	$current_theme = basename( $theme_dir );
	$theme_root = dirname( $theme_dir );

	add_filter( 'theme_root', function() use ( $theme_root ) {
		return $theme_root;
	} );

	register_theme_directory( $theme_root );

	add_filter( 'pre_option_template', function() use ( $current_theme ) {
		return $current_theme;
	});
	add_filter( 'pre_option_stylesheet', function() use ( $current_theme ) {
		return $current_theme;
	});
}
tests_add_filter( 'muplugins_loaded', '_register_theme' );

/**
 * テーマが前提にしているプラグインを読み込む
 *
 * themes/hametuha から見た ../../plugins（ローカルでもコンテナでも同じ位置関係）。
 * 無ければ読み込まず、該当テストはスキップする。
 */
tests_add_filter( 'muplugins_loaded', function () {
	$plugin_dir = dirname( dirname( dirname( __DIR__ ) ) ) . '/plugins';
	foreach ( [ 'two-factor/two-factor.php' ] as $plugin ) {
		if ( file_exists( $plugin_dir . '/' . $plugin ) ) {
			require_once $plugin_dir . '/' . $plugin;
		}
	}
} );


// 管理者の2要素認証による権限制限は Test_TwoFactor 以外では切る（テストはセッションを持たない）
tests_add_filter( 'hametuha_two_factor_enforced', '__return_false' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
