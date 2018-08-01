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
	$diff = floor( $diff / 365 );
	$half = $diff % 365 > 180 ? '半' : '';
	return sprintf( '%d年%s', $diff, $half );
}

