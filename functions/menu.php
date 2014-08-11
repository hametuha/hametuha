<?php

/**
 * メニューの有効化
 */
add_action('init', function(){
	register_nav_menus(array(
		'hametuha_global_works' => 'グローバルナビゲーションの作品の欄に使われます',
		'hametuha_global_about' => 'フッターの破滅派とは？の欄に使われます',
	));
});
