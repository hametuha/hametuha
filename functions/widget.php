<?php
//投稿右端
register_sidebar(array(
	'name' => '投稿サイドバー',
	'id' => 'single-post-sidebar',
	'description' => '投稿シングルページの右側に表示されます',
	'before_widget' => '<div id="%1$s" class="widget %2$s">',
	'after_widget' => '</div>',
	'before_title' => '<h2 class="widget-title">',
	'after_title' => '</h2>'
));
//全ページ右端
register_sidebar(array(
	'name' => '汎用サイドバー',
	'id' => 'general-sidebar',
	'description' => 'ほとんどのページの右側に表示されます',
	'before_widget' => '<div id="%1$s" class="widget %2$s">',
	'after_widget' => '</div>',
	'before_title' => '<h2 class="widget-title">',
	'after_title' => '</h2>'
));
//トップページ中央
register_sidebar(array(
	'name' => 'トップページバナー',
	'id' => 'frontpage-sidebar',
	'description' => 'トップページ中央下に表示されます',
	'before_widget' => '<div id="%1$s" class="grid_3 widget %2$s">',
	'after_widget' => '</div>',
	'before_title' => '<h2 class="widget-title">',
	'after_title' => '</h2>'
));

//アーカイブページ左
register_sidebar(array(
	'name' => 'アーカイブページ',
	'id' => 'left-sidebar',
	'description' => '一覧ページの左側に表示されます',
	'before_widget' => '<div id="%1$s" class="widget %2$s">',
	'after_widget' => '</div>',
	'before_title' => '<h2 class="widget-title">',
	'after_title' => '</h2>'	
));

//掲示板左
register_sidebar(array(
	'name' => '掲示板右',
	'id' => 'thread-sidebar',
	'description' => '掲示板の右に表示されます',
	'before_widget' => '<div id="%1$s" class="widget %2$s">',
	'after_widget' => '</div>',
	'before_title' => '<h2 class="widget-title">',
	'after_title' => '</h2>'	
));
//faq右
register_sidebar(array(
	'name' => 'よくある質問',
	'id' => 'faq-sidebar',
	'description' => 'よくある質問ページの右側に表示されます',
	'before_widget' => '<div id="%1$s" class="widget %2$s">',
	'after_widget' => '</div>',
	'before_title' => '<h2 class="widget-title">',
	'after_title' => '</h2>'	
));
//安否乗除右
register_sidebar(array(
	'name' => '安否情報右',
	'id' => 'anpi-sidebar',
	'description' => '安否情報ページの右側に表示されます',
	'before_widget' => '<div id="%1$s" class="widget %2$s">',
	'after_widget' => '</div>',
	'before_title' => '<h2 class="widget-title">',
	'after_title' => '</h2>'	
));
//告知右
register_sidebar(array(
	'name' => '告知右',
	'id' => 'announcement-sidebar',
	'description' => '告知ページの右側に表示されます',
	'before_widget' => '<div id="%1$s" class="widget %2$s">',
	'after_widget' => '</div>',
	'before_title' => '<h2 class="widget-title">',
	'after_title' => '</h2>'	
));
//フッター
register_sidebar(array(
	'name' => '旧フッターサイドバー',
	'id' => 'footer-sidebar',
	'description' => 'フッターに表示されます。廃止予定。',
	'before_widget' => '<div id="%1$s" class="widget %2$s">',
	'after_widget' => '</div>',
	'before_title' => '<h3 class="widget-title">',
	'after_title' => '</h3>'	
));
//フッター新しい
register_sidebar(array(
	'name' => '新フッターサイドバー',
	'id' => 'footer-sidebar-new',
	'description' => 'フッターに表示されます。3つまで。',
	'before_widget' => '<div id="%1$s" class="widget %2$s">',
	'after_widget' => '</div>',
	'before_title' => '<h3 class="widget-title">',
	'after_title' => '</h3>'	
));

/**
 * ウィジェット登録
 */
function _hametuha_widgets(){
	foreach(scandir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'widgets') as $file){
		if(preg_match("/^[^\.].*\.php$/", $file)){
			get_template_part('widgets/'.str_replace(".php", '', $file));
		}
	}
}
add_action('widgets_init', '_hametuha_widgets');