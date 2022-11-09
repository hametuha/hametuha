<?php
/**
 * 脚注関連
 *
 * @package hametuha
 */

/**
 * 脚注があれば表示する
 *
 * @param string      $before
 * @param string      $after
 * @param string|null $title
 * @param WP_Post     $post
 * @return void
 */
function hametuha_footer_notes( $before = '<aside class="footernote">', $after = '</aside>', $title = null, $post = null ) {
	$footer_notes = hametuha_get_footer_notes( $post );
	if ( ! $footer_notes ) {
		return;
	}
	if ( null === $title ) {
		$title = __( '脚注', 'hametuha' );
	}
	if ( ! empty( $title ) ) {
		$title_html = sprintf( '<h2 class="post-footernote-title">%s</h2>', esc_html( $title ) );
	} else {
		$title_html = '';
	}
	// TODO: Make markdown formatter abstract.
	$converter = new \League\CommonMark\GithubFlavoredMarkdownConverter( [
		'allow_unsafe_links' => false,
	] );
	printf( '%s%s%s%s', $before, $title_html, $converter->convert( $footer_notes ), $after );
}

/**
 * Get footer notes.
 *
 * @param int|null|WP_Post $post Post object.
 * @return string
 */
function hametuha_get_footer_notes( $post = null ) {
	$post = get_post( $post );
	$meta = get_post_meta( $post->ID, '_footernotes', true );
	if ( ! empty( $meta ) ) {
		return $meta;
	}
	$footernotes = [];
	if ( preg_match_all( '#<small class="footernote-ref">(.*?)</small>#u', $post->post_content, $matches, PREG_SET_ORDER ) ) {
		$counter = 0;
		foreach ( $matches as $match ) {
			$counter++;
			$footernotes[] = sprintf( '<li class="footernote-item" id="footernote-%1$d"><a class="footernote-link" href="#noteref-%1$d">%1$d. </a>%2$s</li>', $counter, $match[1] );
		}
	}

	return empty( $footernotes ) ? '' : sprintf( "<ol class='footernote-list'>\n%s\n</ol>", implode( "\n", $footernotes ) );
}

/**
 * Add footernote.
 *
 * @param string $content Post contnet.
 */
add_filter( 'the_content', function( $content ) {
	$counter = 0;
	return preg_replace_callback( '#<small class="footernote-ref">(.*?)</small>#u', function( $matches ) use ( &$counter ) {
		$counter++;
		return sprintf( '<a class="noteref-link" id="noteref-%1$d" href="#footernote-%1$d"><sup>*%1$d</sup></a>', $counter );
	}, $content );
}, 10, 2 );
