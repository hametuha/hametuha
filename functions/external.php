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
	$key   = 'minicome_products';
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

/**
 * Get KDP URL.
 *
 * @param null|int|WP_Post $post Post object.
 * @return string
 */
function hametuha_kdp_url( $post = null ) {
	$post = get_post( $post );
	$url  = 'https://amzn.to/3XqCRt0'; // 破滅派の検索結果へのURL
	if ( ! $post ) {
		return $url;
	}
	return Series::get_instance()->get_kdp_url( $post->ID ) ?: $url;
}

/**
 * Render KDP url.
 *
 * @param null|int|WP_Post $post Post object.
 * @return void
 */
function hametuha_the_kdp_url( $post = null ) {
	echo esc_url( hametuha_kdp_url( $post ) );
}
