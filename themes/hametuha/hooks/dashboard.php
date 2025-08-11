<?php
/**
 * Home screen of hashboard/
 */

/**
 * アドミンバーを常に非表示
 *
 * @filter show_admin_bar
 * @return boolean
 */
add_filter( 'show_admin_bar', '__return_false', 1000 );

/**
 * Display device width.
 */
add_action( 'hashboard_head', function() {
	?>
	<meta name="viewport" content="width=device-width,initial-scale=1.0" />
	<?php
}, 1 );

/**
 * Add links to admin bar.
 */
add_action( 'admin_bar_menu', function ( WP_Admin_Bar &$admin_bar ) {
	if ( ! is_admin() ) {
		return;
	}
	$admin_bar->add_menu( [
		'id'     => 'hashboard-site',
		'parent' => 'site-name',
		'title'  => 'ダッシュボード',
		'href'   => \Hametuha\Hashboard::screen_url(),
		'group'  => false,
	] );
	$admin_bar->add_menu( [
		'id'     => 'bbs',
		'parent' => 'site-name',
		'title'  => '掲示板',
		'href'   => get_post_type_archive_link( 'thread' ),
		'group'  => false,
	] );
	$admin_bar->add_menu( [
		'id'     => 'faqs',
		'parent' => 'site-name',
		'title'  => 'ヘルプセンター',
		'href'   => get_post_type_archive_link( 'faq' ),
		'group'  => false,
	] );
	$admin_bar->add_node( [
		'id'     => 'hashboard-user',
		'parent' => 'user-actions',
		'title'  => 'ダッシュボード',
		'href'   => \Hametuha\Hashboard::screen_url(),
		'group'  => false,
	] );
}, 10 );

/**
 * 新しい画面を追加
 */
add_filter( 'hashboard_screens', function( $screens ) {
	$new_screens = [];
	foreach ( $screens as  $key => $class_name ) {
		if ( 'profile' == $key ) {
			if ( current_user_can( 'edit_posts' ) ) {
				$new_screens['works']      = \Hametuha\Dashboard\Works::class;
				$new_screens['statistics'] = \Hametuha\Dashboard\Statistics::class;
			}
			$new_screens['reading'] = \Hametuha\Dashboard\Readings::class;
			if ( current_user_can( 'edit_posts' ) ) {
				$new_screens['sales']         = \Hametuha\Dashboard\Sales::class;
				$new_screens['notifications'] = \Hametuha\Dashboard\Notifications::class;
				$new_screens['requests']      = \Hametuha\Dashboard\Requests::class;
			}
		}
		$new_screens[ $key ] = $class_name;
	}
	return $new_screens;
} );

/**
 * サイドバーにリンクを追加
 */
add_filter( 'hashboard_sidebar_links', function ( $links ) {
	$new_links   = [];
	$add_divider_after = [
		'reviews'
	];
	foreach ( $links as $key => $html ) {
		if ( 'profile' === $key ) {
			// Add help URL.
			$help_url          = get_page_link( get_page_by_path( 'help' ) );
			$new_links['help'] = <<<HTML
        		 <li class="hb-menu-item">
            		<a href="{$help_url}">
                		<i class="material-icons">live_help</i> ヘルプセンター
            		</a>
        		</li>
HTML;
		}
		if ( in_array( $key, [ 'profile', 'threads', 'notifications' ], true ) ) {
			$new_links[ $key . '-divider'] = '<li class="divider"></li>';
		}
		$new_links[ $key ] = $html;
		if ( 'dashboard' === $key ) {
			$new_links['works-divider'] = '<li class="divider"></li>';
		}
		if ( in_array( $key, $add_divider_after, true ) ) {
			$new_links[ 'divider' . $key ] = "<li class='divider'></li>";
		}
	}

	return $new_links;
} );

/**
 * ダッシュボードをカスタマイズ
 */
add_filter( 'hashboard_dashboard_blocks', function( $blocks ) {
	ob_start();
	?>
	<div class="widget-campaign-list">
		<?php
		foreach ( hametuha_recent_campaigns( 3, false ) as $campaign ) {
			hameplate( 'parts/loop', 'campaign', [
				'campaign' => $campaign,
			] );
		}
		?>
		<div>
			<a href="<?php echo home_url( '/all-campaigns' ); ?>" class="btn btn-secondary btn-block">過去の募集を見る</a>
		</div>
	</div>
	<?php
	$campaigns = ob_get_contents();
	ob_end_clean();
	$blocks = [
		[
			'id'    => 'notification',
			'title' => '通知',
			'size'  => 1,
			'html'  => sprintf(
				'<hametuha-notification-block link="%s"></hametuha-notification-block>',
				home_url( 'dashboard/notifications' )
			),
		],
		[
			'id'    => 'actions',
			'title' => '最近の募集',
			'html'  => $campaigns,
			'size'  => 1,
		],
		[
			'id'    => 'announcement',
			'title' => '告知',
			'html'  => sprintf(
				'<hb-post-list post-type="announcement" more-button="%s" @post-list-updated="updated()" new="7"></hb-post-list>',
				get_post_type_archive_link( 'announcement' )
			),
			'size'  => 1,
		],
	];
	if ( current_user_can( 'edit_posts' ) ) {
		$blocks[] = [
			'id'    => 'recent-works',
			'title' => '最近の作品',
			'html'  => sprintf(
				'<hb-post-list post-type="posts" more-button="%s" author="%d" @post-list-updated="updated()" new="7"></hb-post-list>',
				admin_url( 'edit.php' ),
				get_current_user_id()
			),
			'size'  => 1,
		];
	}
	// Add slack if enabled.
	if ( function_exists( 'hameslack_can_request_invitation' ) && hameslack_can_request_invitation( get_current_user_id() ) ) {
		$blocks[] = [
			'id'    => 'slack',
			'title' => 'Slack登録',
			'html'  => hameplate( 'templates/dashboard/block', 'slack', [], false ),
			'size'  => 1,
		];
	}
	wp_enqueue_script( 'hametuha-hb-dashboard' );
	return $blocks;
} );
