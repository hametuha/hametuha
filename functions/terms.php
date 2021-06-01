<?php
/**
 * Term related functions.
 */


/**
 * Get nouns for news.
 *
 * @param null $post
 * @param bool $filter
 * @return array
 */
function hametuha_get_nouns( $post = null, $filter = true ) {
	$terms = get_the_terms( get_post( $post ), 'nouns' );
	if ( is_wp_error( $terms ) || ! $terms ) {
		return [];
	}
	if ( ! $filter ) {
		return $terms;
	}
	return array_filter(
		$terms,
		function( $term ) {
			return 1 < $term->count;
		}
	);
}
