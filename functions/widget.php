<?php
/**
 * ウィジェットを登録する
 */

//全ページ右端
register_sidebar(array(
	'name' => '汎用サイドバー',
	'id' => 'general-sidebar',
	'description' => 'ほとんどのページの右側に表示されます',
	'before_widget' => '<div id="%1$s" class="widget %2$s">',
	'after_widget' => '</div>',
	'before_title' => '<h2 class="widget-title">',
	'after_title' => '</h2>',
));

//トップページ中央
register_sidebar(array(
	'name' => 'トップページバナー',
	'id' => 'frontpage-sidebar',
	'description' => 'トップページ中央下に表示されます',
	'before_widget' => '<div id="%1$s" class="col-sm-4 col-xs-12 widget %2$s">',
	'after_widget' => '</div>',
	'before_title' => '<h2 class="widget-title">',
	'after_title' => '</h2>',
));

//掲示板右
register_sidebar(array(
	'name' => '掲示板右',
	'id' => 'thread-sidebar',
	'description' => '掲示板の右に表示されます',
	'before_widget' => '<div id="%1$s" class="widget %2$s">',
	'after_widget' => '</div>',
	'before_title' => '<h2 class="widget-title">',
	'after_title' => '</h2>',
));

//faq右
register_sidebar(array(
	'name' => 'よくある質問',
	'id' => 'faq-sidebar',
	'description' => 'よくある質問ページの右側に表示されます',
	'before_widget' => '<div id="%1$s" class="widget %2$s">',
	'after_widget' => '</div>',
	'before_title' => '<h2 class="widget-title">',
	'after_title' => '</h2>',
));
