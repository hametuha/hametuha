<?php

/**
 * ページテンプレートを差し替え
 */
add_filter( 'template_include', function ( $path ) {
	if ( is_singular( 'page' ) && ! is_home() && 'index.php' == basename( $path ) ) {
		$path = get_template_directory() . '/single.php';
	}
	return $path;
} );
