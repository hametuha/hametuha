<?php

/**
 * メニューの有効化
 */
add_action(
	'init',
	function() {
		register_nav_menus(
			[
				'hametuha_global_about' => 'フッターの破滅派とは？の欄に使われます',
				'hamenew_actions'       => 'はめにゅーの勧誘ブロックに使われます',
				'hametuha_sub_globals'  => 'サブナビゲーションに使われます。',
			]
		);
	}
);
