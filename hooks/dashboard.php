<?php
/**
 * Home screen of hashboard/
 */

/**
 * Add links to admin bar.
 */
add_action( 'admin_bar_menu', function ( WP_Admin_Bar &$admin_bar ) {
    if ( ! is_admin() ) {
        return;
    }
	$admin_bar->add_menu( [
		'id' => 'hashboard-site',
		'parent' => 'site-name',
		'title' => 'ダッシュボード',
		'href' => \Hametuha\Hashboard::screen_url(),
		'group' => false,
	] );
	$admin_bar->add_menu( [
		'id' => 'bbs',
		'parent' => 'site-name',
		'title' => '掲示板',
		'href' => get_post_type_archive_link( 'thread' ),
		'group' => false,
	] );
	$admin_bar->add_menu( [
		'id' => 'faqs',
		'parent' => 'site-name',
		'title' => 'ヘルプセンター',
		'href' => get_post_type_archive_link( 'faq' ),
		'group' => false,
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
 * サイドバーにリンクを追加
 */
add_filter( 'hashboard_sidebar_links', function ( $links ) {
	$new_links = [];
	$link_to_add = [
        'dashboard' => [],
    ];
	if ( current_user_can( 'edit_posts' ) ) {
	    $link_to_add['dashboard'][] = [ 'works', 'book', admin_url( 'edit.php' ), 'あなたの作品' ];
    }
	foreach ( $links as $key => $html ) {
		$new_links[ $key ] = $html;
		if ( ! isset( $link_to_add[ $key ] ) || ! $link_to_add[ $key ] ) {
		    continue;
        }
		foreach ( $link_to_add[ $key ] as list( $slug, $icon, $url, $label ) ) {
			$url = esc_url( $url );
			$label = esc_html( $label );
			$new_links[ $slug ] = <<<HTML
						 <li class="hb-menu-item">
                			<a href="{$url}">
								<i class="material-icons">shopping_cart</i> {$label}
                			</a>
						</li>
HTML;
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
		<?php foreach ( hametuha_recent_campaigns( 3, false ) as $campaign ) {
			hameplate( 'parts/loop', 'campaign', [
				'campaign' => $campaign
			] );
		} ?>
		<div>
			<a href="<?= home_url( '/all-campaigns' ) ?>" class="btn btn-secondary btn-block">過去の募集を見る</a>
		</div>
	</div>
	<?php
	$campaigns = ob_get_contents();
	ob_end_clean();
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
			'title' => '最近の募集',
			'html' => $campaigns,
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
    ];
	if ( current_user_can( 'edit_posts' ) ) {
	    $blocks[] = [
			'id' => 'recent-works',
			'title' => '最近の作品',
			'html' => sprintf(
				'<hb-post-list post-type="posts" more-button="%s" author="%d" @post-list-updated="updated()" new="7"></hb-post-list>',
				admin_url( 'edit.php' ),
				get_current_user_id()
			),
			'size' => 1,
		];
    }
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


