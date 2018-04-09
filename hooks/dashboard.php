<?php
/**
 * Home screen of hashboard/
 */


/**
 * 新しい画面を追加
 */
add_filter( 'hashboard_screens', function( $screens ) {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return $screens;
	}
	$new_screens = [];
	foreach ( $screens as  $key => $class_name ) {
		if ( 'profile' == $key ) {
			$new_screens['notifications'] = \Hametuha\Dashboard\Notifications::class;
			$new_screens['statistics'] = \Hametuha\Dashboard\Statistics::class;
			$new_screens['sales'] = \Hametuha\Dashboard\Sales::class;
		}
		$new_screens[ $key ] = $class_name;
	}
	return $new_screens;
} );

/**
 * ダッシュボードをカスタマイズ
 */
add_filter( 'hashboard_dashboard_blocks', function( $blocks ) {

	$blocks = [
		[
			'id' => 'notification',
			'title' => '通知',
			'size' => 1,
			'html' => sprintf(
				'<hametuha-notification-block link="%s"></hametuha-notification-block>',
				home_url( 'dashboard/notifications' )
			),
		],
		[
			'id'   => 'actions',
			'title' => 'アクション',
			'html' => '<p>ああああああああ</p>',
			'size' => 1,
		],
		[
			'id' => 'announcement',
			'title' => '告知',
			'html' => sprintf(
				'<hb-post-list post-type="announcement" more-button="%s" @post-list-updated="updated()" new="7"></hb-post-list>',
				get_post_type_archive_link( 'announcement' )
			),
			'size' => 1,
		],
		[
			'id' => 'recent-works',
			'title' => '最近の作品',
			'html' => sprintf(
				'<hb-post-list post-type="posts" more-button="%s" author="%d" @post-list-updated="updated()" new="7"></hb-post-list>',
				admin_url( 'edit.php' ),
				get_current_user_id()
			),
			'size' => 1,
		],
	];
	// Add slack if enabled.
	if ( function_exists( 'hameslack_can_request_invitation' ) && hameslack_can_request_invitation( get_current_user_id() ) ) {
		$blocks[] = [
			'id' => 'slack',
			'title' => 'Slack登録',
			'html' => hameplate( 'templates/dashboard/block', 'slack', [], false ),
			'size' => 1,
		];
	}
	wp_enqueue_script( 'hametuha-hb-dashboard' );
	return $blocks;
} );
