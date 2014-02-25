<?php
/**
 * 画像、CSS、JSに関するものを記載
 * 
 * 
 * 
 */


//投稿サムネイルを許可
add_theme_support('post-thumbnails', array('post', 'page', 'series', 'announcement'));
set_post_thumbnail_size( 670, 400, false );

//画像のサイズ（小さい）を追加
add_image_size('pinky', 80, 80, true);

/**
 * CSSを読み込む
 */
function _hametuha_style(){
	//統一CSS
	wp_enqueue_style('hametuha-core');
	
	//Theme My login
	if(!is_page('login')){
		wp_dequeue_style('wp_gianism');
		wp_dequeue_style('theme-my-login');
	}
	//iframe
	if(isset($_GET['iframe']) && $_GET['iframe']){
		wp_dequeue_style('admin-bar');
	}
	//投稿を読み込んでいるページ
	if(is_singular('post')){
		wp_dequeue_style('contact-form-7');
		wp_dequeue_style('wp-tmkm-amazon');
	}
	//wp-pagenaviのCSSを打ち消し
	wp_dequeue_style("wp-pagenavi");
}
add_action('wp_print_styles', '_hametuha_style', 1000);

/**
 * スクリプトを読み込む
 */
function _hametuha_enqueue_scripts(){
	//投稿の場合
	if(is_singular('post')){
		wp_dequeue_script('contact-form-7');
		wp_enqueue_style('fancybox');
	}
	if(is_single()){
		wp_enqueue_script('single-post');
	}
	//コメント用
	if(is_singular() && !is_page()){
		wp_enqueue_script( 'comment-reply' );
	}
	//レビュー用
	if(is_page('feedback') || is_page('your-reviews')){
		wp_enqueue_script('review-manager');
	}
	//アドミンバーの非表示
	if(isset($_GET['iframe']) && $_GET['iframe']){
		wp_deregister_script( 'admin-bar' );
		remove_action( 'wp_head', 'wp_admin_bar_header' );
		remove_action( 'wp_head', '_admin_bar_bump_cb' );
		remove_action('wp_footer','wp_admin_bar_render',1000);
	}
	//共通
	wp_enqueue_script('hametuha-common');
}
add_action('wp_enqueue_scripts', '_hametuha_enqueue_scripts', 1000);

/**
 * 管理画面でCSSを読み込む
 */
function _hametuha_admin_style(){
	if(current_user_can('edit_posts')){
		wp_enqueue_style('hametuha-admin-author', get_bloginfo('template_directory')."/css/admin-author.css", array(), HAMETUHA_THEME_VERSION);
	}
}
add_action("admin_print_styles", "_hametuha_admin_style", 200);

/**
 * 管理画面でJSを読み込む
 */
function _hametuha_admin_scripts(){
	if(current_user_can('edit_posts')){
		wp_enqueue_script('hametuha-admin-author', get_bloginfo('template_directory')."/js/admin-author.js", array('jquery'), HAMETUHA_THEME_VERSION);
		wp_localize_script('hametuha-admin-author', 'HamtuhaAdmin', array(
			'isEditor' => current_user_can('edit_others_posts')
		));
	}
}
add_action("admin_enqueue_scripts", '_hametuha_admin_scripts');

/**
 * アセットを登録する
 */
function _hametuha_register_assets(){
	//jQuery UI
	wp_register_script("jquery-effects", get_template_directory_uri()."/js/jquery-effects.js", array('jquery'), "1.8.14", !is_admin());
	wp_register_script("jquery-slider", get_template_directory_uri()."/js/jquery-slider.js", array("jquery", "jquery-ui-core", "jquery-ui-widget", "jquery-ui-mouse"), "1.8.9", !is_admin());
	wp_register_script('jquery-easing', get_template_directory_uri()."/js/jquery-easing.js", array('jquery'), '1.3', !is_admin());
	wp_register_script('jquery-mousewheel', get_template_directory_uri()."/js/jquery-mousewheel.js", array('jquery'), '3.0.6', !is_admin());
	
	//jQuery flicker
	wp_register_script('jquery-flickable', get_template_directory_uri()."/js/jquery-flickable.js", array('jquery'), '1.03', !is_admin());
	
	//Datepicker
	//wp_register_script("jquery-timepicker", get_template_directory_uri()."/js/jquery-ui-timepicker.js",array("jquery-ui-datepicker", "jquery-slider") ,"1.8.9", !is_admin());
	wp_register_style("jquery-ui-smoothness", get_template_directory_uri()."/css/smoothness/jquery-ui.css", array(), "1.8.9");
		
	//リセットCSS
	if(is_ssl()){
		wp_register_style('yui-reset-css', get_template_directory_uri()."/css/reset-min.css", null, '3.3.0');
	}else{
		wp_register_style('yui-reset-css', 'http://yui.yahooapis.com/3.3.0/build/cssreset/reset-min.css', null, null);
	}
	//Google Map
	wp_register_script("gmap", "http://maps.google.com/maps/api/js?sensor=true&language=ja", array(), null, !is_admin());
	//FancyBox
	wp_register_script('fancybox', get_template_directory_uri()."/js/jquery-fancybox.js", array('jquery-easing', 'jquery-mousewheel'), '2.0.6', !is_admin());
	wp_register_style('fancybox', get_template_directory_uri()."/css/fancybox/fancybox.css", array(), '2.0.6');
	//qTip
	wp_register_script('qtip', get_template_directory_uri()."/js/jquery-qtip.js", array('jquery'),'1.0', !is_admin());
	wp_register_style('qtip', get_template_directory_uri()."/css/jquery.qtip.min.css", array(), '1.0');
	//Helper
	wp_register_script('hametuha-common', get_template_directory_uri().'/js/common.js', array('jquery', 'qtip'), HAMETUHA_THEME_VERSION, true);
	wp_register_script('single-post', get_template_directory_uri()."/js/single-post.js", array('fancybox'), HAMETUHA_THEME_VERSION, true);
	wp_register_script('review-manager', get_template_directory_uri().'/js/review-manager.js', array('jquery', 'fancybox'), HAMETUHA_THEME_VERSION, true);	
	//コア
	wp_register_style('hametuha-core', get_template_directory_uri()."/style/stylesheets/core.css", array('yui-reset-css', 'fancybox', 'qtip'), HAMETUHA_THEME_VERSION);
}
add_action('init', '_hametuha_register_assets');


/**
 * ループ内で投稿タイプのラベルを返す
 * @return string
 */
function get_current_post_type_label(){
	$post_type = get_post_type();
	switch($post_type){
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
 * 投稿画面にfaviconを追加
 */
function _hametuha_favicon(){
	?>
	<link rel="shortcut icon" href="<?php echo get_stylesheet_directory_uri(); ?>/img/favicon.ico" />
	<?php
}
add_action('admin_head', '_hametuha_favicon');
add_action('wp_head', '_hametuha_favicon');

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

