<?php
/**
 * Home screen of hashboard/
 */


add_filter( 'hashboard_dashboard_blocks', function( $blocks ) {
	return [
		[
			'id' => 'notification',
			'html' => '通知',
			'size' => 1,
		],
		[
			'id'   => 'actions',
			'html' => 'アクション',
			'size' => 1,
		],
		[
			'id' => 'announcement',
			'html' => '告知',
			'size' => 1,
		],
		[
			'id' => 'recent-works',
			'html' => '最近の作品',
			'size' => 1,
		],
	];
} );
