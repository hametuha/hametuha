<?php
/**
 * Hotfix.
 */


/**
 * Avoid deletion.
 * @see https://blog.ripstech.com/2018/wordpress-file-delete-to-code-execution/
 */ 
add_filter( 'wp_update_attachment_metadata', function( $data ) {
    if( isset($data['thumb']) ) {
        $data['thumb'] = basename($data['thumb']);
    }

    return $data;
	
} );
