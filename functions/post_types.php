<?php

// 投稿フォーマットを登録する
add_theme_support('post-formats', array('image'));

// 固定ページに抜粋を追加する
add_post_type_support('page', 'excerpt');

// 固定ページのコメントを停止
add_action('init', function(){
    remove_post_type_support('page', 'comments');
});

/**
 * 投稿タイプを登録する
 *
 *
 */
add_action('init', function(){
	global $wpdb;
	//シリーズ
	$series = 'series';
	$args = array(
		'label' => '作品集',
		'description' => '著者によってまとめられた作品集です。特定のテーマに基づいた連作や長編小説などがあります。近々ePubなどの形式に書き出せるようになる予定（2012年9月現在）です。',
		'public' => true,
		'menu_position' => 25,
		'supports' => array('title', 'editor', 'author', 'slug', 'thumbnail', 'excerpt'),
		'has_archive' => true,
		'capability_type' => 'post',
		'show_in_menu' => 'edit.php',
		'rewrite' => array('slug' => $series)
	);
	register_post_type($series, $args);
	
	
	//告知
	$annoucement_post_type = 'announcement';
	$args = array(
		'label' => '告知',
		'description' => '破滅派同人による告知です。イベントなどの公式告知情報もあります。',
		'public' => true,
		'menu_position' => 20,
		'menu_icon' => 'dashicons-pressthis',
		'supports' => array('title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments'),
		'has_archive' => true,
		'capability_type' => 'page',
		'rewrite' => array('slug' => $annoucement_post_type)
	);
	register_post_type($annoucement_post_type, $args);


	//よくある質問
	$faq_post_type = 'faq';
	$args = array(
		'label' => 'よくある質問',
		'description' => '破滅派に寄せられた質問です。みなさんの疑問を解決します。',
		'public' => true,
		'menu_position' => 20,
		'menu_icon' => 'dashicons-editor-help',
		'supports' => array('title', 'editor', 'author', 'comments'),
		'has_archive' => true,
		'capability_type' => 'page',
		'rewrite' => array('slug' => $faq_post_type)
	);
	register_post_type($faq_post_type, $args);

	//FAQタクソノミー
	register_taxonomy('faq_cat', array('faq'), array(
		'hierarchical' => false,
		'show_ui' => true,
		'query_var' => true,
		'rewrite' => array( 'slug' => 'faq-cat' ),
		'label' => 'カテゴリー'
	));


	//安否情報
	$args = array(
		'label' => '安否情報',
		'description' => '破滅派同人の安否を知るための最新情報です。書いていない人のことは心配してあげてください。',
		'public' => true,
		'menu_position' => 10,
		'menu_icon' => 'dashicons-microphone',
		'supports' => array('title', 'editor', 'author', 'thumbnail', 'comments' ),
		'has_archive' => true,
		'capability_type' => 'post',
		'rewrite' => array('slug' => 'anpi/archives')
	);
	register_post_type('anpi', $args);

	//安否情報カテゴリー
	register_taxonomy('anpi_cat', array('anpi'), array(
		'hierarchical' => true,
		'show_ui' => true,
		'query_var' => true,
		'rewrite' => array( 'slug' => 'anpi-cat' ),
		'label' => 'カテゴリー'
	));

    // レビュー
    register_taxonomy('review', 'post', array(
        'label' => 'レビューポイント',
        'hierarchical' => false,
        'show_ui' => false,
        'query_var' => true,
        'capabilities' => array(
            'manage_terms' => 'manage_options',
            'edit_terms' => 'manage_options',
            'delete_terms' => 'manage_options',
            'assign_terms' => 'manage_options'
        ),
        'rewrite' => array('slug' => 'review')
    ));
});



/**
 * 長過ぎる文字列を短くして返す
 * @param string $sentence
 * @param int $length
 * @param string $elipsis
 * @return string
 */
function trim_long_sentence($sentence, $length = 100, $elipsis = '…'){
	if(mb_strlen($sentence, 'utf-8') <= $length){
		return $sentence;
	}else{
		return mb_substr($sentence, 0, $length - 1, 'utf-8').$elipsis;
	}
}

/**
 * サブページじゃなければfalse、 サブページの場合は親の投稿IDを返す
 * @global object $post
 * @param mixed $post
 * @return int
 */
function is_subpage($post = null){
	if(is_null($post)){
		global $post;
	}else{
		$post = get_post($post);
	}
	return (int)$post->post_parent;
}

/**
 * 次のシリーズ作品へのリンクを返す
 *
 * @global wpdb $wpdb
 * @global object $post
 * @param string $before
 * @param string $after
 * @param object $post
 * @param boolean $next falseにすると前の作品
 */
function next_series_link($before = '<li>', $after = '</li>', $post = null, $next = true){
	global $wpdb;
    $post = get_post($post);

	$sql = "SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type = 'post' AND post_status = 'publish' AND post_parent = %d";
	$sql .= ($next) ? " AND post_date > %s ORDER BY post_date ASC"
			: " AND post_date < %s ORDER BY post_date DESC";
	$post = $wpdb->get_row($wpdb->prepare($sql, $post->post_parent, $post->post_date));
    $label = $next ? apply_filters('the_title', $post->post_title).' &raquo;'
                   : '&laquo; '.apply_filters('the_title', $post->post_title);
    if( $post ){
        printf('%s<a href="%s">%s</a>%s', $before, get_permalink($post->ID), $label, $after);
    }
}

/**
 * 前の投稿へのリンクを出力する
 * @param string $before
 * @param string $after
 * @param object $post 
 */
function prev_series_link($before = '<li>', $after = '</li>', $post = null){
	next_series_link($before, $after, $post, false);
}


/**
 * よくある質問のタイトルを変える
 * @global object $post
 * @param string $title
 * @param int $id
 * @return string
 */
function _hametuha_faq_title($title, $id = 0){
	if(!is_admin()){
		$post = get_post($id);
		if($post->post_type == 'faq'){
			$title = 'Q. '.$title;
		}
	}
	return $title;
}
add_filter('the_title', '_hametuha_faq_title', 10, 2);


/**
 * 現在のページがプロフィールページか否か
 *
 * @return bool
 */
function hametuha_is_profile_page(){
    return '0' === get_query_var('profile_name');
}

/**
 * 現在のページの種別を返す
 *
 * @return string
 */
function hametuha_page_type(){
    if( is_singular('post') || is_tag() || is_category() ){
        return 'post';
    }elseif( is_singular('anpi') || is_post_type_archive('anpi') || is_tax('anpi_cat') ){
        return 'anpi';
    }elseif( is_singular('thread') || is_post_type_archive('thread') || is_tax('topic') ){
        return 'thread';
    }elseif( is_singular('info') || is_post_type_archive('info') ){
        return 'info';
    }elseif( is_singular('announcemnt') || is_post_type_archive('announcement') ){
        return 'announcement';
    }elseif( is_singular('faq') || is_post_type_archive('faq') || is_tax('faq_cat') ){
        return 'faq';
    }elseif( is_front_page() ){
        return 'front';
    }elseif( is_page() ){
        return 'page';
    }elseif( is_search() ){
        return 'search';
    }else{
        return '';
    }
}