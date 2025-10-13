<?php

/**
 * ページテンプレートを差し替え
 */
add_filter( 'template_include', function ( $path ) {
	if ( is_singular( 'page' ) && ! is_home() && 'index.php' == basename( $path ) ) {
		$path = get_template_directory() . '/single.php';
	} elseif ( is_singular( 'thread' ) ) {
		$path = get_template_directory() . '/templates/thread/single-thread.php';
	} elseif ( is_post_type_archive( 'thread' ) || is_tax( 'topic' ) ) {
		$path = get_template_directory() . '/templates/thread/archive-thread.php';
	} elseif ( is_singular( 'faq' ) ) {
		$path = get_template_directory() . '/templates/faq/single-faq.php';
	} elseif ( is_post_type_archive( 'faq' ) || is_tax( 'faq_cat' ) ) {
		$path = get_template_directory() . '/templates/faq/archive-faq.php';
	}
	return $path;
}, 11 );
