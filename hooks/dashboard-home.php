<?php
/**
 * Home screen of hashboard/
 */


add_filter( 'hashboard_dashboard_blocks', function( $blocks ) {
	return [
		[
			'id' => 'notification',
			'title' => '通知',
			'size' => 1,
		],
		[
			'id'   => 'actions',
			'title' => 'アクション',
			'html' => '<p>ああああああああ',
			'size' => 1,
		],
		[
			'id' => 'announcement',
			'title' => '告知',
			'html' => '<p>ああああああああ<br />いいいいいい<br />ううううう</p>',
			'size' => 1,
		],
		[
			'id' => 'recent-works',
			'title' => '最近の作品',
			'html' => '<p>ああああああああ<br />いいいいいい<br />ううあああああああああああああああああああああああああああああああああああああああううう</p>',
			'size' => 1,
		],
		[
			'id' => 'slack',
			'title' => 'Slack登録',
			'size' => 1,
		],
	];
} );
