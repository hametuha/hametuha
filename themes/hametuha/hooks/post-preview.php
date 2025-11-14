<?php
/**
 * Post preview relatd.
 *
 * @package hametuha
 */

/**
 * Avoid "Private " prefix from title.
 */
add_filter( 'private_title_format', function ( $title, $post ) {
	return '%s';
}, 10, 2 );

/**
 * Display watermark.
 */
add_action( 'wp_footer', function () {
	if ( ! is_singular() ) {
		return;
	}
	$label = '';
	$class = '';
	if ( is_preview() ) {
		$label = 'プレビュー';
	} elseif ( 'private' === get_queried_object()->post_status ) {
		$class = 'red';
		$label = '<i class="fa fa-lock"></i> 非公開';
	}
	if ( $label ) {
		wp_enqueue_script( 'hametuha-watermark' );
		printf( '<div id="watermark" class="%2$s">%1$s</div>', $label, $class );
	}
} );
