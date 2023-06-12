<?php
/**
 * 計算関係の関数
 *
 * @package hametuha
 */

/**
 * 日数の違いを返す
 *
 * @param null $post
 *
 * @return int
 */
function hametuha_date_diff( $post = null ) {
	$post = get_post( $post );
	return ceil( ( current_time( 'timestamp', true ) - strtotime( $post->post_date_gmt ) ) / 86400 );
}

/**
 * Get formatted string how old this post is.
 *
 * @param int  $limit
 * @param null $post
 * @return string
 */
function hametuha_date_diff_formatted( $limit = 0, $post = null ) {
	$diff = hametuha_date_diff( $post );
	if ( $limit && $diff <= $limit ) {
		// Diff is less than limit.
		return '';
	}
	if ( $diff < 365 ) {
		return sprintf( '%d日', $diff );
	}
	$year = floor( $diff / 365 );
	$half = $diff % 365 > 180 ? '半' : '';
	return sprintf( '%d年%s', $year, $half );
}

/**
 * Detect if post should display updated.
 *
 * @param int              $days
 * @param null|int|WP_Post $post
 * @return bool
 */
function hametuha_remarkably_updated( $days = 30, $post = null ) {
	$days = max( 1, absint( $days ) );
	$post = get_post( $post );
	if ( ! $post ) {
		return false;
	}
	$diff = strtotime( $post->post_modified_gmt ) - strtotime( $post->post_date_gmt );
	return ( 60 * 60 * 24 * $days < $diff );
}

/**
 * Check if post is too old.
 *
 * @param int              $days
 * @param null|int|WP_Post $post
 * @return bool
 */
function hametuha_remarkably_old( $days = 30, $post = null ) {
	$post = get_post( $post );
	if ( ! $post || 'publish' !== $post->post_status ) {
		return false;
	}
	$days         = absint( $days );
	$last_updated = max( strtotime( $post->post_date_gmt ), strtotime( $post->post_modified_gmt ) );
	$diff         = current_time( 'timestamp', true ) - $last_updated;
	if ( 0 > $diff ) {
		return false;
	}
	return 60 * 60 * 24 * $days < $diff;
}
