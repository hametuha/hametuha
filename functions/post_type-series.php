<?php
/**
 * シリーズに関する処理／関数群
 */

use Hametuha\Model\Series;

/**
 * シリーズに属しているか否かを返す。属している場合は親ID
 *
 * @param WP_Post $post
 *
 * @return int
 */
function is_series( $post = null ) {
	$post = get_post( $post );

	return 'series' == get_post_type( $post->post_parent ) ? $post->post_parent : 0;
}

/**
 * シリーズが終了しているか
 *
 * @param null $post
 *
 * @return bool
 */
function is_series_finished( $post = null ) {
	$post = get_post( $post );
	if ( 'series' == $post->post_type ) {
		$series_id = $post->ID;
	} else {
		$series_id = $post->post_parent;
	}

	return Series::get_instance()->is_finished( $series_id );
}

/**
 * Convert post query arguments.
 *
 * @param array $args
 * @return array
 */
function hametuha_series_args( $args ) {
	$args = wp_parse_args( $args, [
		'excludes'       => 0,
		'author'         => 0,
		'author_not'     => 0,
		'posts_per_page' => -1,
		'paged'          => 1,
	] );
	$query_args = [
		'post_type'      => 'series',
		'post_status'    => 'publish',
		'meta_query'     => [
			[
				'key'   => '_kdp_status',
				'value' => 2,
			],
		],
		'paged'          => $args['paged'],
		'posts_per_page' => $args['posts_per_page'],
		'orderby' => [
			'menu_order' => 'DESC',
			'date'       => 'DESC',
		],
	];
	if ( $args['excludes'] ) {
		$query_args['post__not_in'] = array_map( 'intval', (array) $args['excludes'] );
	}
	if ( $args['author'] ) {
		$query_args['author'] = $args['author'];
	}
	if ( $args['author_not'] ) {
		$query_args['author__not_in'] = $args[ 'author_not' ];
	}
	return $query_args;
}

/**
 *
 *
 * @param array $args
 * @return WP_Query
 */
function hametuha_series_query( $args ) {
	return new WP_Query( hametuha_series_args( $args ) );
}

/**
 * シリーズに属している場合にシリーズページへのリンクを返す
 *
 * @param string $pre
 * @param string $after
 * @param WP_Post $post
 */
function the_series( $pre = '', $after = '', $post = null ) {
	$series = is_series( $post );
	if ( $series ) {
		$series = get_post( $series );
		echo $pre . '<a href="' . get_permalink( $series->ID ) . '" itemprop="isPartOf">' . apply_filters( 'the_title', $series->post_title ) . '</a>' . $after;
	}
}

/**
 * Get KDP price
 *
 * @param null|int|WP_Post $post
 */
function the_series_price( $post = null ) {
	$price = get_series_price( $post );
	echo false !== $price ? number_format( $price ) : 'N/A';
}

/**
 * 販売価格を記載する
 *
 * @param null|int|WP_Post $post
 * @return false|int
 */
function get_series_price( $post = null ) {
	$post = get_post( $post );
	$price = get_post_meta( $post->ID, '_kdp_price', true );
	return ! is_numeric( $price ) ? false : (int) $price;
}

/**
 * Amazonに記載されている料金を取得する
 *
 * @deprecated
 * @param null $post
 * @param bool|true $cache
 *
 * @return false|int
 */
function get_kdp_remote_price( $post = null, $cache = true ) {
	$post   = get_post( $post );
	$series = Series::get_instance();
	if ( 2 != $series->get_status( $post->ID ) ) {
		return false;
	}
	$key   = 'kdp_price_' . $post->ID;
	$price = get_transient( $key );
	if ( false === $price || ! $cache ) {
		$url      = $series->get_kdp_url( $post->ID );
		$response = wp_remote_get( $url );
		if ( is_wp_error( $response ) || ! preg_match( '#<(span|b)([^>]*?)class="a-color-price"([^>]*?)>([^<]+)</(span|b)>#', $response['body'], $match ) ) {
			return false;
		}
		$price = preg_replace( '#[^0-9]#', '', $match[4] );
		if ( is_numeric( $price ) ) {
			set_transient( $key, $price, 60 * 60 );
			$price = intval( $price );
		} else {
			return false;
		}
	}
	return $price;
}

/**
 * 実際の価格に合っているかを返す
 *
 * @param null|int|WP_Post $post
 *
 * @return bool
 */
function is_series_price_unmatch( $post = null ) {
	$post = get_post( $post );
	$request_price = get_post_meta( $post->ID, '_kdp_required_price', true );
	$real_price    = get_post_meta( $post->ID, '_kdp_price', true );
	if ( ! ( is_numeric( $real_price ) && is_numeric( $request_price ) ) ) {
		return false;
	}
	return $real_price != $request_price;
}

/**
 * Get all user for series
 *
 * @deprecated
 * @param null|WP_Post|int $post
 *
 * @return array
 */
function get_series_authors( $post = null ) {
	$post = get_post( $post );

	return \Hametuha\Model\Collaborators::get_instance()->get_published_collaborators( $post->ID );
}

/**
 * Get series genre.
 *
 * @param null|int|WP_Post $post
 * @return WP_Term[]
 */
function hametuha_get_series_categories( $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return [];
	}
	global $wpdb;
	$query = <<<SQL
		SELECT t.*, tt.*
		FROM {$wpdb->terms} AS t
		INNER JOIN {$wpdb->term_taxonomy} AS tt
		ON t.term_id = tt.term_id
		INNER JOIN (
		    SELECT tr.term_taxonomy_id,
		           COUNT( tr.object_id ) as `post_count`
		    FROM {$wpdb->term_relationships} AS tr
		    LEFT JOIN {$wpdb->posts} AS p
		    ON tr.object_id = p.ID
		    WHERE p.post_type = 'post'
		      AND p.post_status IN ( 'publish', 'private' )
		      AND p.post_parent = %d
		    GROUP BY tr.term_taxonomy_id
		) AS relationships 
		ON relationships.term_taxonomy_id = tt.term_taxonomy_id
		WHERE tt.taxonomy = 'category'
		ORDER BY relationships.post_count DESC
SQL;
	$results = $wpdb->get_results( $wpdb->prepare( $query, $post->ID ) );
	return array_map( function( $term ) {
		return new WP_Term( $term );
	}, $results );

}

/**
 * Show series range
 *
 * @param null|WP_Post|int $post
 * @param string $format
 */
function the_series_range( $post = null, $format = '' ) {
	$post   = get_post( $post );
	$format = $format ?: get_option( 'date_format' );
	$range  = Series::get_instance()->get_series_range( $post->ID );
	if ( $range && $range->start_date ) {
		echo mysql2date( $format, $range->start_date ) . '〜' . mysql2date( $format, $range->last_date );
	}
}

/**
 * リダイレクトされるのを防ぐ
 *
 * @param string $redirect_url
 *
 * @return string
 */
add_filter( 'redirect_canonical', function ( $redirect_url ) {
	if ( is_singular( 'series' ) && false !== strpos( $_SERVER['REQUEST_URI'], '/page/' ) ) {
		return false;
	} else {
		return $redirect_url;
	}
} );


/**
 * シリーズをみられないようにする
 *
 * @param string $content
 *
 * @return string
 */
function hametuha_series_hide( $content ) {
	// DOMの一部を切り出す
	$dom       = \WPametu\Utility\Formatter::get_dom( $content );
	$body      = $dom->getElementsByTagName( 'body' )->item( 0 );
	$dom_count = $body->childNodes->length;
	$limit     = floor( $dom_count / 4 );
	for ( $i = $dom_count - 1; $i >= 0; $i -- ) {
		if ( $i > $limit ) {
			$body->removeChild( $body->childNodes->item( $i ) );
		}
	}
	$content = \WPametu\Utility\Formatter::to_string( $dom );
	$content .= "\n<div class=\"content-hide-cover\"></div>";
	remove_filter( 'the_content', 'hametuha_series_hide' );

	return $content;
}


