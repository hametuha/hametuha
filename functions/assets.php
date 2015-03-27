<?php
/**
 * 画像、CSS、JSに関するものを記載
 * 
 * 
 * 
 */




//画像のサイズ（小さい）を追加
add_image_size('pinky', 160, 160, true);

// プロフィール写真を追加
add_image_size('profile', 300, 300, true);

/**
 * 選択できる画像サイズを追加
 *
 * @param array $sizes
 * @return array
 */
add_filter( 'image_size_names_choose', function($sizes){
	$sizes['pinky'] = '小型正方形';
	return $sizes;
});


/**
 * アセットを登録する
 *
 * @action init
 */
add_action('init', function(){

    // Modernizr
    wp_register_script('modernizr', get_template_directory_uri().'/assets/js/modernizr.min.js', null, '2.8.2', false);

    // Twitter Bootstrap
    wp_register_script('twitter-bootstrap', get_template_directory_uri().'/assets/js/bootstrap.min.js', ['jquery'], '3.1.1', true);

    // FontPlus
    wp_register_script('font-plus', '//webfont.fontplus.jp/accessor/script/fontplus.js?xnZANi~MEp8%3D&aa=1', null, null, false);

    // メインJS
    wp_register_script('hametuha-common', get_template_directory_uri().'/assets/js/common.min.js', ['twitter-bootstrap', 'backbone', 'modernizr', 'font-plus', 'jsrender'], hametuha_version(), true);

    // シングルページ用JS
    wp_register_script('hametuha-single', get_template_directory_uri().'/assets/js/single-post.min.js', ['hametuha-common', 'chart-js', 'jquery-form', 'jquery-touch-punch', 'jquery-ui-slider'], hametuha_version(), true);

    // 告知ページ用JS
    wp_register_script('hametuha-announcement', get_template_directory_uri().'/assets/js/single-announcement'.hametuha_min_ext(), ['jquery', 'gmap'], hametuha_version(), true);
    wp_localize_script('hametuha-announcement', 'HametuhaAnnouncement', [
        'icon' => get_template_directory_uri().'/assets/img/facebook-logo.png',
    ]);

    // ログイン名変更用JS
    wp_register_script('hametuha-login-changer', get_template_directory_uri().'/assets/js/components/login-change-helper'.hametuha_min_ext(), ['jquery-form', 'hametuha-common']);

    // 同人になる用JS
    wp_register_script('hametuha-become-author', get_template_directory_uri().'/assets/js/components/become-author'.hametuha_min_ext(), ['jquery-form', 'hametuha-common']);

    // フロントページ用JS
    wp_register_script('hametuha-front', get_template_directory_uri().'/assets/js/components/front-helper'.hametuha_min_ext(), ['jquery-masonry', 'imagesloaded', 'chart-js']);

    // メインCSS
    wp_register_style('hametuha-app', get_template_directory_uri().'/assets/css/app.css', null, hametuha_version());

    // 管理画面用CSS
    wp_register_style('hametuha-admin', get_template_directory_uri().'/assets/css/admin.css', null, hametuha_version());

	// プロフィール変更用JS
	wp_register_script('hametuha-user-edit', get_template_directory_uri().'/assets/js/components/edit-profile-helper'.hametuha_min_ext(), ['jquery-effects-highlight']);

	// 投稿編集画面
	wp_register_script('hametuha-edit-form', get_template_directory_uri().'/assets/js/admin/author'.hametuha_min_ext(), ['jquery-cookie'], hametuha_version(), true);

});


/**
 * CSSを読み込む
 */
add_action('wp_enqueue_scripts', function(){
	//統一CSS
	wp_enqueue_style('hametuha-app');

	//Theme My login
    wp_dequeue_style('theme-my-login');

	//iframe
	if(isset($_GET['iframe']) && $_GET['iframe']){
		wp_dequeue_style('admin-bar');
	}
	//投稿を読み込んでいるページ
	if( is_singular('post') ){
		wp_dequeue_style('contact-form-7');
		wp_dequeue_style('wp-tmkm-amazon');
	}
	//wp-pagenaviのCSSを打ち消し
	wp_dequeue_style("wp-pagenavi");
}, 1000);



/**
 * スクリプトを読み込む
 *
 */
add_action('wp_enqueue_scripts', function(){

	//共通
	wp_enqueue_script('hametuha-common');

	//投稿の場合
	if( is_singular('post') ){
		wp_dequeue_script('contact-form-7');
        wp_enqueue_script('hametuha-single');
	}

    // トップページ
    if( is_front_page() ){
        wp_enqueue_script('hametuha-front');
    }

	//コメント用
	if( is_singular() && !is_page() ){
        wp_enqueue_script( 'comment-reply' );
	}

    // 告知用JS
    if( is_singular('announcement') ){
        wp_enqueue_script('hametuha-announcement');
    }

}, 1000);

/**
 * 管理画面でアセットを読み込む
 */
add_action("admin_enqueue_scripts", function( $page = '' ){

	$screen = get_current_screen();

    wp_enqueue_style('hametuha-admin');

	if( current_user_can('edit_posts') ){

	}

	// 編集画面
	if( 'post' == $screen->base ){
		wp_enqueue_script('hametuha-edit-form');
	}

	// プロフィール編集画面
	if( 'user-edit.php' == $page ){
		wp_enqueue_media();
		wp_enqueue_script('hametuha-user-edit');
	}
}, 200);




/**
 * IE8以下用のJS
 *
 * @action wp_head
 */
add_action('wp_head', function(){
    $shiv = get_template_directory_uri().'/assets/vendor/html5shiv/dist/html5shiv.min.js';
    $respond = get_template_directory_uri().'/assets/vendor/respondjs/dest/respond.min.js';
    echo <<<EOS
<!--[if lt IE 9]>
  <script src="{$shiv}?ver=3.7.0"></script>
  <script src="{$respond}?ver=1.4.2"></script>
<![endif]-->
EOS;
}, 20);



/**
 * デバッグ環境ならminをつける
 *
 * @param string $ext
 * @return string
 */
function hametuha_min_ext($ext = 'js'){
    $ext = '.'.$ext;
    if( !WP_DEBUG ){
        $ext = '.min'.$ext;
    }
    return $ext;
}


/**
 * ループ内で投稿タイプのラベルを返す
 * @return string
 */
function get_current_post_type_label(){
	$post_type = get_post_type();
	switch( $post_type ){
		case 'info':
		case 'faq':
		case 'announcement':
			$post_type = get_post_type_object($post_type);
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
 * @param mixed $post
 * @return boolean
 */
function has_image_attachment($post = null){
	if(is_null($post)){
		global $post;
	}else{
		$post = get_post($post);
	}
	global $wpdb;
	$sql = "SELECT ID FORM {$wpdb->posts} WHERE post_parent = %d AND post_type = 'attachment' AND post_mime_type LIKE 'image%'";
	return (boolean)$wpdb->get_var($wpdb->prepare($sql, $post->ID));
}

