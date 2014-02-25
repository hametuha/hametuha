<?php

//投稿フォーマットを登録する
add_theme_support('post-formats', array('image'));

/**
 * 投稿タイプを登録する
 * @global wpdb $wpdb 
 */
function _hametuha_post_types(){
	global $wpdb;
	//シリーズ
	$serise = 'series';
	$args = array(
		'label' => '作品集',
		'labels' => array(
			'name' => '作品集',
			'add_new' => '新規追加',
			'add_new_item' => '新しい作品集を追加',
			'edit_item' => '作品集を編集',
			'new_item' => '新しい作品集',
			'view_item' => '作品集を表示',
			'search_items' => '作品集を検索',
			'not_found' => '作品集は見つかりませんでした',
			'not_found_in_trash' => 'ゴミ箱に作品集は見つかりませんでした'
		),
		'description' => '著者によってまとめられた作品集です。特定のテーマに基づいた連作や長編小説などがあります。近々ePubなどの形式に書き出せるようになる予定（2012年9月現在）です。',
		'public' => true,
		'menu_position' => 25,
		'menu_icon' => get_bloginfo('template_directory').'/img/admin/icon-series.png',
		'supports' => array('title', 'editor', 'author', 'slug', 'thumbnail', 'excerpt'),
		'has_archive' => true,
		'capability_type' => 'post',
		'show_in_menu' => 'edit.php',
		'rewrite' => array('slug' => $serise)
	);
	register_post_type($serise, $args);
	
	
	//公式告知
	$annoucement_post_type = 'announcement';
	$args = array(
		'label' => '告知',
		'labels' => array(
			'name' => '告知',
			'add_new' => '新規追加',
			'add_new_item' => '新しい告知を追加',
			'edit_item' => '告知を編集',
			'new_item' => '新しい告知',
			'view_item' => '告知を表示',
			'search_items' => '告知を検索',
			'not_found' => '告知は見つかりませんでした',
			'not_found_in_trash' => 'ゴミ箱に告知は見つかりませんでした'
		),
		'description' => '破滅派同人による告知です。公募などの公式告知情報もあります。',
		'public' => true,
		'menu_position' => 20,
		'menu_icon' => get_bloginfo('template_directory').'/img/admin/icon-announcement.png',
		'supports' => array('title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments'),
		'has_archive' => true,
		'capability_type' => 'post',
		'rewrite' => array('slug' => $annoucement_post_type)
	);
	register_post_type($annoucement_post_type, $args);
	
	
	//お知らせ
	$args = array(
		'label' => 'おしらせ',
		'labels' => array(
			'name' => 'おしらせ'
		),
		'description' => '破滅派からのおしらせです。業務連絡やサイト運営に関することが中心となりなます。',
		'public' => true,
		'menu_position' => 20,
		'menu_icon' => get_bloginfo('template_directory').'/img/admin/icon-info.png',
		'supports' => array('title', 'editor', 'author'),
		'has_archive' => true,
		'capability_type' => 'page',
		'rewrite' => array('slug' => 'info')
	);
	register_post_type('info', $args);
	//文芸ニュース
	$args = array(
		'label' => '文芸ニュース',
		'labels' => array(
			'name' => '文芸ニュース'
		),
		'description' => '文芸ニュースを破滅派独自の視点で紹介します。気になる方はタレこんでください。',
		'public' => true,
		'menu_position' => 20,
		'menu_icon' => get_bloginfo('template_directory').'/img/admin/icon-news.png',
		'supports' => array('title', 'editor', 'author'),
		'has_archive' => true,
		'capability_type' => 'page',
		'rewrite' => array('slug' => 'news')
	);
	register_post_type('news', $args);
	//よくある質問
	$faq_post_type = 'faq';
	$args = array(
		'label' => 'よくある質問',
		'labels' => array(
			'name' => 'よくある質問'
		),
		'description' => '破滅派に寄せられた質問です。みなさんの疑問を解決します。',
		'public' => true,
		'menu_position' => 20,
		'menu_icon' => get_bloginfo('template_directory').'/img/admin/icon-faq.png',
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
		'labels' => array(
			'name' => 'カテゴリー',
			'singular_name' => 'カテゴリー',
			'search_items' =>  'カテゴリーを検索',
			'popular_items' => 'よく使われるカテゴリー',
			'all_items' => 'すべてのカテゴリー',
			'parent_item' => null,
			'parent_item_colon' => null,
			'edit_item' => 'カテゴリーを編集', 
			'update_item' => 'カテゴリーを更新',
			'add_new_item' => '新しいカテゴリーを追加',
			'new_item_name' => '新規カテゴリー',
			'separate_items_with_commas' => 'カテゴリーをカンマで区切ってください',
			'add_or_remove_items' => 'カテゴリーの追加および削除',
			'choose_from_most_used' => 'よく使われるものから選ぶ'
		)
	));
	//安否情報
	$args = array(
		'label' => '安否情報',
		'labels' => array(
			'name' => '安否情報',
			'add_new' => '新規追加',
			'add_new_item' => '新しい安否情報を追加',
			'edit_item' => '安否情報を編集',
			'new_item' => '新しい安否情報',
			'view_item' => 'この安否情報を表示',
			'search_items' => '安否情報を検索',
			'not_found' => '安否情報は見つかりませんでした',
			'not_found_in_trash' => 'ゴミ箱に安否情報は見つかりませんでした'
		),
		'description' => '破滅派同人の安否を知るための最新情報です。書いていない人のことは心配してあげてください。',
		'public' => true,
		'menu_position' => 10,
		//'menu_icon' => get_bloginfo('template_directory').'/img/admin/icon-faq.png',
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
		'labels' => array(
			'name' => 'カテゴリー',
			'singular_name' => 'カテゴリー',
			'search_items' =>  'カテゴリーを検索',
			'popular_items' => 'よく使われるカテゴリー',
			'all_items' => 'すべてのカテゴリー',
			'parent_item' => null,
			'parent_item_colon' => null,
			'edit_item' => 'カテゴリーを編集', 
			'update_item' => 'カテゴリーを更新',
			'add_new_item' => '新しいカテゴリーを追加',
			'new_item_name' => '新規カテゴリー',
			'separate_items_with_commas' => 'カテゴリーをカンマで区切ってください',
			'add_or_remove_items' => 'カテゴリーの追加および削除',
			'choose_from_most_used' => 'よく使われるものから選ぶ'
		)
	));
}
add_action('init', '_hametuha_post_types');


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
 * メタボックス追加時に起きる関数
 */
function _hametuha_post_type_metabox(){
	
}
add_action('add_meta_boxes', '_hametuha_post_type_metabox');

/**
 * シリーズに属しているか否かを返す。属している場合は親ID
 * @global wpdb $wpdb
 * @global object $post
 * @param object $post
 * @return int
 */
function is_series($post = null){
	global $wpdb;
	if(is_null($post)){
		global $post;
	}else{
		$post = get_post($post);
	}
	return (int)$wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_type = 'series' AND ID = %d", $post->post_parent));
}

/**
 * 次のシリーズ作品へのリンクを返す
 * @global wpdb $wpdb
 * @global object $post
 * @param string $before
 * @param string $after
 * @param object $post
 * @param boolean $next falseにすると前の作品
 */
function next_series_link($before = '<li>', $after = '</li>', $post = null, $next = true){
	global $wpdb;
	if(is_null($post)){
		global $post;
	}else{
		$post = get_post($post);
	}
	$sql = "SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type = 'post' AND post_status = 'publish' AND post_parent = %d";
	$sql .= ($next) ? " AND post_date > %s ORDER BY post_date ASC"
			: " AND post_date < %s ORDER BY post_date DESC";
	$next = $wpdb->get_row($wpdb->prepare($sql, $post->post_parent, $post->post_date));
	if($next){
		echo $before.'<a href="'.get_permalink($next->ID).'">'.apply_filters('the_title', $next->post_title).'</a>'.$after;
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
 * シリーズに属している場合にシリーズページへのリンクを返す
 * @global wpdb $wpdb
 * @param string $pre
 * @param string $after
 * @param object $post 
 */
function the_series($pre = '', $after = '', $post = null){
	global $wpdb;
	$series = is_series($post);
	if($series){
		$series = get_post($series);
		echo $pre.'<a href="'.get_permalink($series->ID).'">'.apply_filters("the_title", $series->post_title).'</a>'.$after;
	}
}

/**
 * シリーズを選ぶセレクトボックスを表示する
 * @global object $post
 * @global wpdb $wpdb 
 */
function _hametuha_post_type_chooser(){
	global $post, $wpdb;
	if($post->post_type == 'post'){
		$current_post_parent = $post->post_parent;
		?>
		<div class="misc-pub-section misc-pub-section-last series-setter">
		<?php
		$serieses = $wpdb->get_results($wpdb->prepare("SELECT ID, post_title, post_parent FROM {$wpdb->posts} WHERE post_type = 'series' AND post_author = %d ORDER BY ID ASC", $post->post_author));
		if(!empty($serieses)): 
		?>
			<label>
				作品集名: 
				<select name="series_id">
					<option value="0"<?php if($current_post_parent == 0) echo ' selected="selected"';?>>なし</option>
					<?php foreach($serieses as $series): ?>
					<option value="<?php echo $series->ID; ?>"<?php if($current_post_parent == $series->ID) echo ' selected="selected"';?>><?php echo esc_html($series->post_title); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
		<?php else: ?>
			<label>作品集名: まだ作品集を<a href="<?php echo admin_url('post-new.php?post_type=series');?>">登録</a>していません。</label>
		<?php endif; ?>
		</div>
		<?php
	}
}
add_action('post_submitbox_misc_actions', '_hametuha_post_type_chooser');

/**
 * 投稿にシリーズを付与する
 * @param int $post_id 
 */
function _hametuha_set_series_to($post_id){
	if(wp_is_post_autosave($post_id) || wp_is_post_revision( $post_id )){
		return;
	}
	if(isset($_REQUEST['series_id'], $_REQUEST['post_ID']) && $post_id == $_REQUEST['post_ID']){
		remove_action('save_post', '_hametuha_set_series_to');
		$req = wp_update_post(array(
			'ID' => intval($_REQUEST['post_ID']),
			'post_parent' => intval($_REQUEST['series_id'])
		));
	}
}
add_action('save_post', '_hametuha_set_series_to');

/**
 * リダイレクトされるのを防ぐ
 * @param string $redirect_url
 * @return string
 */
function _hametuha_canonical($redirect_url){
	if(is_singular('series') && false !== strpos($_SERVER['REQUEST_URI'], '/page/')){
		return false;
	}else{
		return $redirect_url;
	}
}
add_filter('redirect_canonical', '_hametuha_canonical');

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