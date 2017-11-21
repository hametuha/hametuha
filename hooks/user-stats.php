<?php

/**
 * 新しい画面を追加
 */
add_filter( 'hashboard_screens', function( $screens ) {
	$new_screens = [];
	foreach ( $screens as  $key => $class_name ) {
		if ( 'profile' == $key ) {
			$new_screens['statistics'] = \Hametuha\Dashboard\Statistics::class;
			$new_screens['sales'] = \Hametuha\Dashboard\Sales::class;
		}
		$new_screens[ $key ] = $class_name;
	}
	return $new_screens;
} );
