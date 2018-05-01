<?php

/**
 * Add oembed as hametuha
 */
add_action( 'after_setup_theme', function() {
	// Register self embed
	wp_embed_register_handler( 'hametuha', '#https?://hametuha\.(info|com)/(.*?)$#u', function( $match, $attr, $url ) {
		$allowed_post_types = [
			'post',
			'news',
		];
		$post_id = url_to_postid( $url );
		if ( ! $post_id || false === array_search( get_post( $post_id )->post_type, $allowed_post_types ) ) {
			return sprintf(
				'<a href="%s">%s</a>',
				esc_attr( $url ),
				esc_html( 20 < strlen( $url ) ? mb_substr( $url, 0, 30, 'utf-8' ) . '&hellip;' : $url )
			);
		}
		$post = get_post( $post_id );
		return hametuha_format_html_indent_for_embed( hameplate( 'parts/embed', $post->post_type, [
			'object' => $post,
			'url'    => $url,
		], false ) );
	}, true );

	// Add minico.me
	wp_embed_register_handler( 'minicome', '#https?://minico\.me/(.*?)$#u', function( $match, $attr, $url ) {
		$use_cache = ! WP_DEBUG;
		$cache = wp_cache_get( $url, 'minicome' );
		if ( ! $use_cache || false === $cache ) {
			try {
				$response = wp_remote_get( $url );
				if ( is_wp_error( $response ) ) {
					throw new Exception( $response->get_error_message(), 500 );
				}
				$html5 = new \Masterminds\HTML5();
				$dom = $html5->loadHTML( $response['body'] );
				$json = null;
				foreach ( $dom->getElementsByTagName( 'script' ) as $script ) {
					$string = $script->nodeValue;
					if ( false !== strpos( $string, 'Product' ) ) {
						$json = json_decode( trim( $string ) );
						break;
					}
				}
				if ( ! $json ) {
					throw new Exception( '見つかりませんでした', 404 );
				}
				$cache = hametuha_format_html_indent_for_embed( hameplate( 'parts/embed', 'minicome', [
					'url' => $url,
				    'schema' => $json,
				], false ) );
				if ( $use_cache ) {
					wp_cache_set( $url, $cache, 'minicome', 60 * 30 );
				}
			} catch ( Exception $e ) {
				$cache = sprintf( '<a href="%s">%s</a>', esc_url( $url ), hametuha_grab_domain( $url ) );
			}
		}
		return $cache;
	}, true );
} );


