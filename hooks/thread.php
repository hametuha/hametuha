<?php
/**
 * スレッド関連の処理
 *
 * @package hametuha
 */

/**
 * Change thread setting.
 */
add_filter( 'hamethread_post_setting', function( $args ) {
	$args['description'] = '破滅派BBSは参加者達が意見交換をする場所です。積極的にご参加ください。匿名での投稿もできます。';
	return $args;
} );