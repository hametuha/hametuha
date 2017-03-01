<?php

/**
 * アセットを登録する
 *
 * @action init
 */
add_action( 'init', function () {

	// Modernizr
	wp_register_script( 'modernizr', get_template_directory_uri() . '/assets/js/dist/modernizr.js', null, '2.8.3', false );

	// Twitter Bootstrap
	wp_register_script( 'twitter-bootstrap', get_template_directory_uri() . '/assets/js/dist/bootstrap.js', [ 'jquery' ], '3.3.4', true );

	// FontPlus
	wp_register_script( 'font-plus', '//webfont.fontplus.jp/accessor/script/fontplus.js?xnZANi~MEp8%3D&aa=1', null, null, false );

	// Angular
	wp_register_script( 'angular', get_template_directory_uri() . '/assets/js/dist/angular.js', null, '1.4.8', true );

	/**
	 * hametuha_angular_extensions
	 *
	 * @since 5.2.24
	 * @package hametuha
	 * @param array $modules Default ui.bootstrap
	 * @return array
	 */
	$modules = apply_filters( 'hametuha_angular_extensions', [ 'ui.bootstrap' ] );
	$modules = implode( ', ', array_map( function( $module ) {
		return sprintf( "'%s'", esc_js( $module ) );
	}, $modules ) );
	$angular_scripts = <<<JS
angular.module('hametuha', [{$modules}]);
JS;
	wp_add_inline_script( 'angular', $angular_scripts );

	// Select2
	wp_register_script( 'select2', get_template_directory_uri().'/assets/js/dist/select2/select2.min.js', [ 'jquery' ], '4.0.3', true );
	wp_register_style( 'select2', get_template_directory_uri().'/assets/css/select2.min.css', [], '4.0.3' );

	// メインJS
	wp_register_script( 'hametuha-common', get_template_directory_uri() . '/assets/js/dist/common.js', [
		'twitter-bootstrap',
		'wp-api',
		'modernizr',
		'font-plus',
		'jsrender',
	], hametuha_version(), true );
	wp_localize_script('hametuha-common', 'HametuhaGlobal', [
		'angularTemplateDir' => preg_replace( '#^(https?://)s\.#u', '$1', get_template_directory_uri() ).'/assets/js/tpl/',
	]);

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

	/// Editor
	wp_register_script( 'hameditor', get_template_directory_uri() . '/assets/js/dist/editor/common.js', [
		'jquery',
		'angular',
		'wp-api',
	], hametuha_version(), true );

	// イベント参加
	wp_register_script( 'hamevent', get_template_directory_uri(). '/assets/js/dist/components/event-participate.js', [
		'angular',
		'wp-api',
	], hametuha_version(), true );

	// メインCSS
	wp_register_style( 'hametuha-app', get_template_directory_uri() . '/assets/css/app.css', null, hametuha_version() );

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
	wp_register_script( 'hametuha-edit-form', get_template_directory_uri() . '/assets/js/dist/admin/editor.js', [ 'jquery-cookie' ], hametuha_version(), true );

} );


/**
 * CSSを読み込む
 */
add_action( 'wp_enqueue_scripts', function () {
	//統一CSS
	wp_enqueue_style( 'hametuha-app' );

	//Theme My login
	wp_dequeue_style( 'theme-my-login' );

	//iframe
	if ( isset( $_GET['iframe'] ) && $_GET['iframe'] ) {
		wp_dequeue_style( 'admin-bar' );
	}
	//投稿を読み込んでいるページ
	if ( is_singular( 'post' ) ) {
		wp_dequeue_style( 'contact-form-7' );
		wp_dequeue_style( 'wp-tmkm-amazon' );
	}
	//wp-pagenaviのCSSを打ち消し
	wp_dequeue_style( 'wp-pagenavi' );
}, 1000 );


/**
 * スクリプトを読み込む
 *
 */
add_action( 'wp_enqueue_scripts', function () {
	//共通
	wp_enqueue_script( 'hametuha-common' );
	//投稿の場合
	if ( is_singular( 'post' ) ) {
		wp_dequeue_script( 'contact-form-7' );
		wp_enqueue_script( 'hametuha-single' );
	}
	// トップページ
	if ( is_front_page() ) {
		wp_enqueue_script( 'hametuha-front' );
	}
	// シリーズ
	if ( is_singular( 'series' ) ) {
		wp_enqueue_script( 'hametuha-series' );
	}
	//コメント用
	if ( is_singular() && ! is_page() ) {
		wp_enqueue_script( 'comment-reply' );
	}
}, 1000 );

/**
 * 管理画面でアセットを読み込む
 */
add_action( "admin_enqueue_scripts", function ( $page = '' ) {

	$screen = get_current_screen();

	wp_enqueue_style( 'hametuha-admin' );

	if ( current_user_can( 'edit_posts' ) ) {

	}

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
 * IE8以下用のJS
 *
 * @action wp_head
 */
add_action( 'wp_head', function () {
	$shiv = get_template_directory_uri() . '/assets/js/dist/html5shiv.js';
	$respond = get_template_directory_uri() . '/assets/js/dist/respond.src.js';
	echo <<<EOS
<!--[if lt IE 9]>
  <script src="{$shiv}?ver=3.7.0"></script>
  <script src="{$respond}?ver=1.4.2"></script>
<![endif]-->
EOS;
}, 20 );
