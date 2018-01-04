<?php
/**
 * ユーザーのmeta_capをいじる
 */

add_filter( 'map_meta_cap', function( $caps, $cap, $user_id, $args ) {
	$remove_cap = function( $caps, $cap ) {
		$index = array_search( $cap, $caps );
		if ( false !== $index ) {
			array_splice( $caps, $index, 1 );
		}
		return $caps;
	};
	switch ( $cap ) {
		case 'publish_epub':
			list( $post_id ) = $args;
			$caps = $remove_cap( $caps, $cap );
			$post = get_post( $post_id );
			if ( $post->post_author != $user_id ) {
				$caps[] = 'edit_others_posts';
			} elseif ( hametuha_is_secret_book( $post ) ) {
				$caps[] = 'edit_posts';
			} else {
				$caps[] = 'manage_options';
			}
			break;
		case 'get_epub':
			$caps = $remove_cap( $caps, $cap );
			list( $file_id ) = $args;
			$file = \Hametuha\Model\CompiledFiles::get_instance()->get_file( $file_id );
			if ( ! $file || ! ( ( $post = get_post( $file->post_id ) ) && 'series' == $post->post_type ) ) {
				$caps[] = 'do_not_allow';
			} else if ( $post->post_author != $user_id ) {
				$caps[] = 'edit_others_posts';
			} else if ( hametuha_is_secret_book( $post ) ) {
				$caps[] = 'edit_posts';
			} else {
				$caps[] = 'manage_options';
			}
			break;
		default:
			// Do nothing.
			break;
	}
	return $caps;
}, 10, 4 );
