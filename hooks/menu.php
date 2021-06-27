<?php
/**
 * Menu related functions
 *
 * @package hametuha
 */

/**
 * メニューの有効化
 */
add_action( 'init', function() {
	register_nav_menus( [
		'hametuha_global_about' => 'フッターの破滅派とは？の欄に使われます',
		'hamenew_actions'       => 'はめにゅーの勧誘ブロックに使われます',
		'hametuha_sub_globals'  => 'サブナビゲーションに使われます。',
		'hametuha_socials'      => __( 'フッターのソーシャルリンクです。', 'hametuha' ),
	] );
} );

// If possible, enable menu caching.
if ( class_exists( 'Kunoichi\\SetMenu' ) ) {
	Kunoichi\SetMenu::enable();
}


/**
 * Change link text in nav menu.
 *
 * @param string   $title Original Title.
 * @param WP_Post  $item  Menu item.
 * @param stdClass $args  Menu arguments.
 * @param int      $depth Depth.
 * @return string
 */
add_filter( 'nav_menu_item_title', function( $title, $item, $args, $depth ) {
	switch ( $args->theme_location ) {
		case 'hametuha_socials':
			$domain = '____';
			if ( preg_match( '#https?://(www\.)?([^/.]+)\.#u', $item->url, $matches ) ) {
				list( $all, $subdomain, $maybe_domain ) = $matches;
				$domain                                 = $maybe_domain;
			}
			$title = sprintf( '<span class="sr-only">%s</span> %s', $title, hametuha_brand_svg( $domain, 24 ) );
			break;
	}
	return $title;
}, 10, 4 );

/**
 * Add count class for menues.
 */
add_filter( 'wp_nav_menu_args', function( $args ) {
	var_dump( $args );
	return $args;
} );
