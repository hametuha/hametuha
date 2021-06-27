<?php

/**
 * アセットを登録する
 *
 * @action init
 */
add_action( 'init', function() {

	// FontPlus.
	wp_register_script( 'font-plus', '//webfont.fontplus.jp/accessor/script/fontplus.js?xnZANi~MEp8%3D&aa=1', null, null, false );

	// Angular.
	wp_register_script( 'angular', get_template_directory_uri() . '/assets/js/dist/angular.js', null, '1.4.8', true );

	// Bootbox.
	wp_register_script( 'bootbox', get_template_directory_uri() . '/dist/vendor/bootbox/bootbox.all.min.js', [ 'jquery', 'bootstrap' ], '5.2.2', true );

	// Headroom
	wp_register_script( 'headroom', get_template_directory_uri() . '/dist/vendor/headroom/headroom.min.js', [ 'jquery' ], '0.12.0', true );

	// FontAwesome
	wp_register_style( 'font-awesome', 'https://use.fontawesome.com/releases/v5.6.3/css/all.css', [], '5.6.3' );
	add_filter( 'style_loader_tag', function ( $tag, $handle, $src, $media ) {
		if ( 'font-awesome' !== $handle ) {
			return $tag;
		}

		return str_replace( '/>', "integrity='sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/' crossorigin='anonymous' />", $tag );
	}, 10, 4 );

	// Prop Types.
	wp_register_script( 'prop-types', get_template_directory() . '/dist/vendor/prop-types/prop-types.min.js', [ 'wp-element' ], '15.7.2', true );

	// jQuery mmenu
	wp_register_script( 'jquery.mmenu', get_template_directory() . '/dist/vendor/mmenu/prop-types.min.js', [ 'jquery' ], '5.6.3', true );

	// Load wp-scripts.
	$json = get_template_directory() . '/wp-dependencies.json';
	if ( file_exists( $json ) ) {
		foreach ( json_decode( file_get_contents( $json ), true ) as $setting ) {
			$url = get_template_directory_uri() . '/' . $setting['path'];
			switch ( $setting['ext'] ) {
				case 'css':
					wp_register_style( $setting['handle'], $url, $setting['deps'], $setting['hash'], $setting['media'] );
					break;
				case 'js':
					wp_register_script( $setting['handle'], $url, $setting['deps'], $setting['hash'], $setting['footer'] );
					break;
			}
		}
	};

	/**
	 * hametuha_angular_extensions
	 *
	 * @param array $modules Default ui.bootstrap
	 *
	 * @return array
	 * @since 5.2.24
	 * @package hametuha
	 */
	$modules = apply_filters( 'hametuha_angular_extensions', [ 'ui.bootstrap' ] );
	$modules = implode(
		', ',
		array_map(
			function ( $module ) {
				return sprintf( "'%s'", esc_js( $module ) );
			},
			$modules
		)
	);
	$angular_scripts = <<<JS
angular.module('hametuha', [{$modules}]);
JS;
	wp_add_inline_script( 'angular', $angular_scripts );

	// Select2
	wp_register_script( 'select2', get_template_directory_uri() . '/dist/vendor/select2/select2.min.js', [ 'jquery' ], '4.0.3', true );
	wp_register_style( 'select2', get_template_directory_uri() . '/dist/vendor/select2/select2.min.css', [], '4.0.3' );

	// メインJS
	wp_register_script( 'hametuha-common', get_template_directory_uri() . '/assets/js/dist/common.js', [
			'twitter-bootstrap',
			'wp-api',
			'font-plus',
			'jsrender',
			'hametuheader',
		],
		hametuha_version(),
		true
	);
	wp_localize_script( 'hametuha-common', 'HametuhaGlobal', [
		'angularTemplateDir' => preg_replace( '#^(https?://)s\.#u', '$1', get_template_directory_uri() ) . '/assets/js/tpl/',
	] );
}, 9 );

/**
 * Load Hashboard assets.
 */
add_action( 'hashboard_head', function() {
	wp_enqueue_style( 'hametuha-hashboard' );
} );

/**
 * スクリプトを読み込む
 *
 */
add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_style( 'hametuha-critical' );
	// Common Style.
	wp_enqueue_style( 'hametuha-app' );
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
 *
 * @param string $page Page name.
 */
add_action( 'admin_enqueue_scripts', function( $page = '' ) {
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
