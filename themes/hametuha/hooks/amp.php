<?php
/**
 * AMP関連関数。フォールバック専用
 *
 * @package hametuha
 */

/**
 * Add fallback rewrite rules.
 */
add_filter( 'rewrite_rules_array', function ( $rules ) {
	return array_merge( [
		'^news/(\d+)/amp/?$' => 'index.php?p=$matches[1]&post_type=news',
	], $rules );
}, 9999 );
