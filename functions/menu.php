<?php

/**
 * メニューの有効化
 */
function _hametuha_nav_menus(){
	register_nav_menus(array(
		'hametuha_global_works' => 'グローバルナビゲーションの作品の欄に使われます',
		'hametuha_global_about' => 'グローバルナビゲーションの破滅派とは？の欄に使われます',
		'hametuha_global_info' => 'グローバルナビゲーションのお知らせの欄に使われます'
	));
}
add_action('init', '_hametuha_nav_menus');
