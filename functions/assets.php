<?php
/**
 * 画像、CSS、JSに関するものを記載
 *
 *
 *
 */


//画像のサイズ（小さい）を追加
add_image_size( 'pinky', 160, 160, true );

// プロフィール写真を追加
add_image_size( 'profile', 300, 300, true );

// ePub表紙用画像
add_image_size( 'kindle-cover', 1200, 1920, true );

/**
 * 選択できる画像サイズを追加
 *
 * @param array $sizes
 *
 * @return array
 */
add_filter( 'image_size_names_choose', function ( $sizes ) {
	$sizes['pinky'] = '小型正方形';
	return $sizes;
} );


/**
 * アセットを登録する
 *
 * @action init
 */
add_action( 'init', function () {

	// Modernizr
	wp_register_script( 'modernizr', get_template_directory_uri() . '/assets/js/dist/modernizr.js', null, '2.8.3', false );

	// Twitter Bootstrap
	wp_register_script( 'twitter-bootstrap', get_template_directory_uri() . '/bower_components/bootstrap-sass/assets/javascripts/bootstrap.min.js', [ 'jquery' ], '3.3.3', true );

	// Bootstrap Notify
	wp_register_script( 'bootstrap-notify', get_template_directory_uri() . '/bower_components/remarkable-bootstrap-notify/bootstrap-notify.min.js', [ 'twitter-bootstrap' ], '3.1.3', true );

	// Bootbox
	wp_register_script( 'bootbox', get_template_directory_uri() . '/assets/js/dist/bootbox.js', [ 'twitter-bootstrap' ], '4.4.0', true );

	// FontPlus
	wp_register_script( 'font-plus', '//webfont.fontplus.jp/accessor/script/fontplus.js?xnZANi~MEp8%3D&aa=1', null, null, false );

	// Angular
	$angular_path = get_template_directory_uri() . '/bower_components/angular/angular'.( WP_DEBUG ? '' : '.min' ).'.js';
	wp_register_script( 'angular', $angular_path, null, '1.4.7', true );

	// Angular Bootstrap
	$angular_bs_path = get_template_directory_uri() . '/bower_components/angular-bootstrap/ui-bootstrap-tpls'.( WP_DEBUG ? '' : '.min' ).'.js';
	wp_register_script( 'angular-bootstrap', $angular_bs_path, [ 'angular' ], '0.14.3', true );

	// メインJS
	wp_register_script( 'hametuha-common', get_template_directory_uri() . '/assets/js/dist/common.js', [
		'twitter-bootstrap',
		'bootbox',
		'backbone',
		'modernizr',
		'font-plus',
		'jsrender',
	    'bootstrap-notify',
	], hametuha_version(), true );

	// シングルページ用JS
	wp_register_script( 'hametuha-single', get_template_directory_uri() . '/assets/js/dist/single-post.js', [
		'hametuha-common',
		'chart-js',
		'jquery-form',
		'jquery-touch-punch',
		'jquery-ui-slider',
	], hametuha_version(), true );

	// 告知ページ用JS
	wp_register_script( 'hametuha-announcement', get_template_directory_uri() . '/assets/js/dist/single-announcement.js', [
		'jquery',
		'gmap',
	], hametuha_version(), true );
	wp_localize_script( 'hametuha-announcement', 'HametuhaAnnouncement', [
		'icon' => get_template_directory_uri() . '/assets/img/facebook-logo.png',
	] );

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
	wp_dequeue_style( "wp-pagenavi" );
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
	if( is_singular( 'series' ) ){
		wp_enqueue_script('hametuha-series');
	}
	//コメント用
	if ( is_singular() && ! is_page() ) {
		wp_enqueue_script( 'comment-reply' );
	}
	// 告知用JS
	if ( is_singular( 'announcement' ) ) {
		wp_enqueue_script( 'hametuha-announcement' );
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
	$shiv = get_template_directory_uri() . '/bower_components/html5shiv/dist/html5shiv.min.js';
	$respond = get_template_directory_uri() . '/bower_components/respond/dest/respond.min.js';
	echo <<<EOS
<!--[if lt IE 9]>
  <script src="{$shiv}?ver=3.7.0"></script>
  <script src="{$respond}?ver=1.4.2"></script>
<![endif]-->
EOS;
}, 20 );


/**
 * デバッグ環境ならminをつける
 *
 * @param string $ext
 *
 * @return string
 */
function hametuha_min_ext( $ext = 'js' ) {
	$ext = '.' . $ext;
	if ( ! WP_DEBUG ) {
		$ext = '.min' . $ext;
	}

	return $ext;
}


/**
 * ループ内で投稿タイプのラベルを返す
 * @return string
 */
function get_current_post_type_label() {
	$post_type = get_post_type();
	switch ( $post_type ) {
		case 'info':
		case 'faq':
		case 'announcement':
			$post_type = get_post_type_object( $post_type );

			return $post_type->labels->singular_name;
			break;
		default:
			return "作品";
			break;
	}
}


/**
 * 投稿が少なくとも一つの画像を持っているか否か
 * @global object $post
 * @global wpdb $wpdb
 *
 * @param mixed $post
 *
 * @return boolean
 */
function has_image_attachment( $post = null ) {
	if ( is_null( $post ) ) {
		global $post;
	} else {
		$post = get_post( $post );
	}
	global $wpdb;
	$sql = "SELECT ID FORM {$wpdb->posts} WHERE post_parent = %d AND post_type = 'attachment' AND post_mime_type LIKE 'image%'";

	return (boolean) $wpdb->get_var( $wpdb->prepare( $sql, $post->ID ) );
}

/**
 * media_side_load_imageのパクリ
 *
 * GIF非対応のため
 *
 * @since 2.6.0
 *
 * @see media_sideload_image
 * @param string $file The URL of the image to download
 * @param int $post_id The post ID the media is to be associated with
 * @param string $desc Optional. Description of the image
 * @return int|WP_Error Attachment ID or WP_Error on failure
 */
function hametuha_sideload_image( $file, $post_id, $desc = null ) {
	// 写真アップロード用のライブラリを読み込み
	require_once( ABSPATH . 'wp-admin/includes/file.php' );
	require_once( ABSPATH . 'wp-admin/includes/media.php' );
	require_once( ABSPATH . 'wp-admin/includes/image.php' );
	if ( empty( $file ) ) {
		return new WP_Error( 500, 'ファイル名が指定されていません。', [ 'status' => 500 ] );
	}
	// Fix for external image
	$http = parse_url( $file );
	if ( isset( $http['query'] ) && ! empty( $http['query'] ) ) {
		parse_str( $http['query'], $str );
		if ( isset( $str['url'] ) ) {
			$file = rawurldecode( $str['url'] );
		}
	}
	// Set variables for storage, fix file filename for query strings.
	preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png|gif)\b/i', $file, $matches );
	$file_array = array();
	$file_array['name'] = basename( $matches[0] );
	// Download file to temp location.
	$file_array['tmp_name'] = download_url( $file );
	// If error storing temporarily, return the error.
	if ( is_wp_error( $file_array['tmp_name'] ) ) {
		return $file_array['tmp_name'];
	}
	// Do the validation and storage stuff.
	$id = media_handle_sideload( $file_array, $post_id, $desc );
	// If error storing permanently, unlink.
	if ( is_wp_error( $id ) ) {
		unlink( $file_array['tmp_name'] );
	}
	return $id;
}

/**
 * 仮のサムネイルを取得する
 *
 * @param null|int|WP_Post $post
 * @deprecated
 *
 * @return array
 */
function get_pseudo_thumbnail( $post = null ) {
	static $thumbnails = array();
	static $index = 0;
	$post = get_post( $post );
	if ( has_post_thumbnail( $post->ID ) ) {
		$src = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'medium' );

		return [
			'action' => 'original',
			'label'  => 'original',
			'url'    => $src[0],
			'value'  => 1,
		];
	} else {
		if ( rand( 1, 10 ) < 6 ) {
			// サムネイルをあえて出さない
			return [
				'action' => 'no-thumb',
				'label'  => 'no-thumb',
				'url'    => '',
				'value'  => 1,
			];
		} else {
			// 仮のサムネイルを出す
			if ( ! $thumbnails ) {
				$files = array_filter( scandir( get_stylesheet_directory() . '/assets/img/thumbs' ), function ( $file ) {
					return 0 !== strpos( $file, '.' );
				} );
				shuffle( $files );
				$thumbnails = $files;
			}
			// インデックスをリセット
			if ( $index >= count( $thumbnails ) ) {
				$index = 0;
			}
			$src = $thumbnails[ $index ];
			$index ++;

			return [
				'action' => 'pseudo',
				'label'  => $src,
				'url'    => get_stylesheet_directory_uri() . '/assets/img/thumbs/' . $src,
				'value'  => 1,
			];
		}
	}
}
