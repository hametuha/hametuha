<?php

/**
 * Display device width.
 */
function hametuha_viewport() {
	?>
	<meta name="viewport" content="width=device-width,initial-scale=1.0" />
	<?php
}
add_action( 'wp_head', 'hametuha_viewport', 1 );
add_action( 'hashboard_head', 'hametuha_viewport', 1 );

/**
 * アセットを登録する
 *
 * @action init
 */
add_action( 'init', function () {

	// Bootstrap 5 (no jQuery dependency)
	wp_register_script( 'twitter-bootstrap', get_template_directory_uri() . '/assets/js/dist/bootstrap.js', [], '5.3.8', true );

	// FontPlus
	wp_register_script( 'font-plus', '//webfont.fontplus.jp/accessor/script/fontplus.js?xnZANi~MEp8%3D&aa=1&chws=1', null, null, false );

	// FontAwesome
	wp_register_style( 'font-awesome', 'https://use.fontawesome.com/releases/v5.6.3/css/all.css', [], '5.6.3' );
	add_filter( 'style_loader_tag', function( $tag, $handle, $src, $media ) {
		if ( 'font-awesome' !== $handle ) {
			return $tag;
		}
		return str_replace( '/>', "integrity='sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/' crossorigin='anonymous' />", $tag );
	}, 10, 4 );

	// Prop Types
	wp_register_script( 'prop-types', 'https://unpkg.com/prop-types/prop-types.min.js', [ 'wp-element' ], null, true );

	// Recharts
	wp_register_script( 'recharts', 'https://unpkg.com/recharts/umd/Recharts.js', [ 'prop-types' ], null, true );

	// Select2
	wp_register_script( 'select2-src', get_template_directory_uri() . '/assets/js/dist/select2/select2.min.js', [ 'jquery' ], '4.0.3', true );
	wp_register_script( 'select2', get_template_directory_uri() . '/assets/js/dist/select2/i18n/ja.js', [ 'select2-src' ], '4.0.3', true );
	wp_register_style( 'select2', get_template_directory_uri() . '/assets/css/select2.min.css', [], '4.0.3' );


	// メインJS
	wp_register_script( 'hametuha-common', get_template_directory_uri() . '/assets/js/dist/common.js', [
		'twitter-bootstrap',
		'wp-api',
		'font-plus',
		'jsrender',
		'hametuheader',
	], hametuha_version(), true );
	wp_localize_script('hametuha-common', 'HametuhaGlobal', []);

	// シングルページ用JS
	wp_register_script( 'hametuha-single', get_template_directory_uri() . '/assets/js/dist/single-post.js', [
		'hametuha-common',
		'chart-js',
		'jquery-form',
		'jquery-touch-punch',
		'jquery-ui-slider',
	], hametuha_version(), true );

	// ログイン名変更用JS
	wp_register_script( 'hametuha-login-changer', get_template_directory_uri() . '/assets/js/dist/components/login-change-helper.js', [
		'jquery-form',
		'hametuha-common',
	], hametuha_version(), true );

	// 同人になる用JS
	wp_register_script( 'hametuha-become-author', get_template_directory_uri() . '/assets/js/dist/components/become-author.js', [
		'jquery-form',
		'hametuha-common',
	], hametuha_version(), true );

	// フロントページ用JS
	wp_register_script( 'hametuha-front', get_template_directory_uri() . '/assets/js/dist/components/front-helper.js', [
		'jquery-masonry',
		'imagesloaded',
		'chart-js',
	], hametuha_version(), true );

	// シリーズ用JS
	wp_register_script( 'hametuha-series', get_template_directory_uri() . '/assets/js/dist/components/series-helper.js', [
		'jquery-masonry',
		'jquery-form',
	], hametuha_version(), true );

	// ソーシャル計測
	wp_register_script( 'hametuha-social', get_template_directory_uri() . '/assets/js/dist/social.js', [ 'jquery' ], hametuha_version(), true );


	// メインCSS
	wp_register_style( 'hametuha-app', get_template_directory_uri() . '/assets/css/app.css', [ 'font-awesome' ], hametuha_version() );

	// 管理画面用CSS
	wp_register_style( 'hametuha-admin', get_template_directory_uri() . '/assets/css/admin.css', null, hametuha_version() );

	// プロフィール変更用JS
	wp_register_script( 'hametuha-user-edit', get_template_directory_uri() . '/assets/js/dist/components/edit-profile-helper.js', [ 'jquery-effects-highlight' ] );

	// フォローボタン
	$path = '/assets/js/dist/components/follow-toggle.js';
	wp_register_script( 'hametu-follow', get_stylesheet_directory_uri() . $path, [
		'twitter-bootstrap',
		'wp-api',
	], filemtime( get_stylesheet_directory() . $path ), true );

	// 投稿編集画面
	wp_register_script( 'hametuha-edit-form', get_template_directory_uri() . '/assets/js/dist/admin/editor.js', [ 'jquery' ], hametuha_version(), true );

	// Watermark
	wp_register_script( 'hametuha-watermark', get_template_directory_uri() . '/assets/js/dist/components/watermark.js', [ 'jquery' ], hametuha_version(), true );

	// 税金関係書類のCSS
	wp_register_style( 'hametuha-accounting-paper', get_template_directory_uri() . '/assets/css/proof.css', [ 'hametuha-app' ], hametuha_version(), 'all' );


	// Register all hashboard.
	wp_register_style( 'hametuha-hashboard', get_template_directory_uri() . '/assets/css/hashboard.css', [], hametuha_version() );

	// Load wp-dependencies.json.
	$deps = get_template_directory() . '/wp-dependencies.json';
	if ( file_exists( $deps ) ) {
		$assets = json_decode( file_get_contents( $deps ), true );
		if ( ! empty( $assets ) ) {
			foreach ( $assets as $asset ) {
				if ( empty( $asset['path'] ) ) {
					continue;
				}
				switch ( $asset['ext'] ) {
					case 'js':
						wp_register_script( $asset['handle'], trailingslashit( get_template_directory_uri() ) . $asset['path'], $asset['deps'], $asset['hash'], $asset['footer'] );
						break;
					case 'css':
						wp_register_style( $asset['handle'], trailingslashit( get_template_directory_uri() ) . $asset['path'], $asset['deps'], $asset['hash'], $asset['media'] ?? 'all' );
						break;
				}
			}
		}
	}
	// todo: fix wpdeps

	// Add custom script.
	wp_localize_script( 'hametuha-components', 'HametuhaComponents', [
		'indicator' => get_template_directory_uri() . '/vendor/hametuha/hashboard/assets/img/ripple.gif',
	] );
}, 9 );

/**
 * スクリプトを読み込む
 *
 */
add_action( 'wp_enqueue_scripts', function () {
	// Common Style.
	if ( ! get_query_var( 'hashboard' ) ) {
		wp_enqueue_style( 'hametuha-app' );
	}

	// Social scripts
	wp_enqueue_script( 'hametuha-social' );

	// Common Scripts
	wp_enqueue_script( 'hametuha-common' );
	// Single post.
	if ( is_singular( 'post' ) ) {
		wp_enqueue_script( 'hametuha-single' );
	}
	// Front page.
	if ( is_front_page() ) {
		wp_enqueue_script( 'hametuha-front' );
	}
	// Series.
	if ( is_singular( 'series' ) ) {
		wp_enqueue_script( 'hametuha-series' );
	}
	// Comment.
	if ( is_singular() && ! is_page() ) {
		wp_enqueue_script( 'comment-reply' );
	}
}, 1000 );

/**
 * Dequeue Assets.
 */
add_action( 'wp_enqueue_scripts', function() {
	// If contact form is not
	if ( ! is_singular() || ! has_shortcode( get_queried_object()->post_content, 'contact-form-7' ) ) {
		wp_dequeue_style( 'contact-form-7' );
		wp_dequeue_script( 'contact-form-7' );
		wp_dequeue_script( 'google-recaptcha' );
		wp_dequeue_script( 'wpcf7-recaptcha' );
	}
	// wp-pagenavi
	wp_dequeue_style( 'wp-pagenavi' );
	// iframe
	if ( isset( $_GET['iframe'] ) && $_GET['iframe'] ) {
		wp_dequeue_style( 'admin-bar' );
	}
	// Single Page.
	wp_dequeue_style( 'wp-tmkm-amazon' );
}, 1001 );

/**
 * 管理画面でアセットを読み込む
 */
add_action( 'admin_enqueue_scripts', function ( $page = '' ) {

	$screen = get_current_screen();

	wp_enqueue_style( 'hametuha-admin' );

	// 編集画面
	if ( 'post' == $screen->base ) {
		wp_enqueue_script( 'hametuha-edit-form' );
	}

	// プロフィール編集画面
	if ( 'user-edit.php' == $page ) {
		wp_enqueue_media();
		wp_enqueue_script( 'hametuha-user-edit' );
	}
}, 200 );

/**
 * Hashboard用にCSSを読み込む
 *
 * @todo hashboard v1.0.0になったらかえる？
 */
add_action( 'hashboard_enqueue_scripts', function() {
	wp_enqueue_style( 'hametuha-hashboard' );
} );

/**
 * jQueryをフッターに動かす
 */
add_action( 'init', function() {
	if ( is_admin() || 'wp-login.php' === basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
		return;
	}
	global $wp_scripts;
	$jquery     = $wp_scripts->registered['jquery-core'];
	$jquery_ver = $jquery->ver;
	$jquery_src = $jquery->src;
	// Register jQuery again.
	wp_deregister_script( 'jquery' );
	wp_register_script( 'jquery', false, [ 'jquery-core' ], $jquery_ver, true );
	wp_deregister_script( 'jquery-core' );
	wp_register_script( 'jquery-core', $jquery_src, [], $jquery_ver, true );
} );
