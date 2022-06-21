<?php
/**
 * Communicate with external sites.
 *
 * @package hametuha
 */

use Hametuha\Model\Series;

/**
 * Get minicome products.
 */
function hametuha_get_minicome_product() {
	$key = 'minicome_products';
	$cache = get_transient( 'minicome_products' );
	if ( false !== $cache ) {
		return $cache;
	}
	if ( ! function_exists( 'fetch_feed' ) ) {
		require_once( ABSPATH . WPINC . '/feed.php' );
	}
	//https://minico.me/feed/?post_type=product
	$endpoint = add_query_arg( [
		'post_type' => 'product',
	], 'https://minico.me/feed/' );
	// Fetch.
	$result = fetch_feed( $endpoint );
	if ( is_wp_error( $result ) ) {
		return $result;
	}
	// Convert to array.
	return array_map( function( SimplePie_Item $item ) {
		// Check price.
		$price     = '';
		$price_tag = $item->get_item_tags( 'http://base.google.com/ns/1.0', 'price' );
		if ( $price_tag ) {
			foreach ( $price_tag as $tag ) {
				$price = $tag['data'];
			}
		}
		// Fetch media.
		$media      = [];
		$media_tags = $item->get_item_tags( 'http://search.yahoo.com/mrss/', 'group' );
		if ( $media_tags ) {
			foreach ( $media_tags as $tag ) {
				if ( ! empty( $tag['child']['http://search.yahoo.com/mrss/']['content'] ) ) {
					foreach ( $tag['child']['http://search.yahoo.com/mrss/']['content'] as $image ) {
						$media[ $image['attribs']['']['size'] ] = $image['attribs'][''];
					}
				}
			}
		}
		return [
			'url'         => $item->get_permalink(),
			'title'       => $item->get_title(),
			'description' => $item->get_description(),
			'media'       => $media,
			'price'       => $price,
		];
	}, $result->get_items() );
}
