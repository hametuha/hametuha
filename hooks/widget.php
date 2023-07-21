<?php
/**
 * ウィジェットを登録する
 */

//全ページ右端
register_sidebar(array(
	'name'          => '汎用サイドバー',
	'id'            => 'general-sidebar',
	'description'   => 'ほとんどのページの右側に表示されます',
	'before_widget' => '<div id="%1$s" class="widget %2$s">',
	'after_widget'  => '</div>',
	'before_title'  => '<h2 class="widget-title">',
	'after_title'   => '</h2>',
));

//トップページ中央
register_sidebar(array(
	'name'          => 'トップページバナー',
	'id'            => 'frontpage-sidebar',
	'description'   => 'トップページ中央下に表示されます',
	'before_widget' => '<div id="%1$s" class="col-sm-4 col-xs-12 widget %2$s">',
	'after_widget'  => '</div>',
	'before_title'  => '<h2 class="widget-title">',
	'after_title'   => '</h2>',
));

// ニュース右
register_sidebar( [
	'name'          => 'ニュース右',
	'id'            => 'news-sidebar',
	'description'   => 'ニュースページの右に表示されます',
	'before_widget' => '<div id="%1$s" class="widget %2$s">',
	'after_widget'  => '</div>',
	'before_title'  => '<h2 class="widget-title">',
	'after_title'   => '</h2>',
] );

// FAQ右
register_sidebar( [
	'name'          => 'FAQ右',
	'id'            => 'faq-sidebar',
	'description'   => 'FAQの右に表示されます。',
	'before_widget' => '<div id="%1$s" class="widget %2$s">',
	'after_widget'  => '</div>',
	'before_title'  => '<h2 class="widget-title">',
	'after_title'   => '</h2>',
] );

// BBS右
register_sidebar( [
	'name'          => 'BBS右',
	'id'            => 'thread-sidebar',
	'description'   => '掲示板の右に表示されます。',
	'before_widget' => '<div id="%1$s" class="widget %2$s">',
	'after_widget'  => '</div>',
	'before_title'  => '<h2 class="widget-title">',
	'after_title'   => '</h2>',
] );
