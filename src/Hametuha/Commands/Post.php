<?php

namespace Hametuha\Commands;


use WPametu\Utility\Command;
use cli\Table;

class Post extends Command {

	const COMMAND_NAME = 'hampost';

	/**
	 * Show statistic for specific condition
	 *
	 * ## OPTIONS
	 *
	 * : <taxonomy>
	 *   taxonomy
	 *
	 * : <term>
	 *   Term ID
	 *
	 * @synopsis <taxonomy> <term>
	 * @param array $args
	 * @param array $assoc
	 */
	public function statistic( $args, $assoc ) {
		list( $taxonomy, $term_id ) = $args;
		$term = get_term_by( 'id', $term_id, $taxonomy );
		if ( ! $term ) {
			self::e( sprintf( 'failed to get term %d of %s', $term_id, $taxonomy ) );
		}
		$posts = get_posts([
			'post_type' => 'post',
			'post_status' => 'any',
		    'tax_query' => [
		    	[
		    		'taxonomy' => $taxonomy,
			        'terms' => (int) $term_id,
			    ],
		    ],
		    'posts_per_page' => -1,
		]);
		if ( ! $posts ) {
			self::e( 'No post found.' );
		}
		$table = new Table();
		$table->setHeaders( [ 'ID', 'Length', '' ] );
		$total = 0;
		$length = 0;
		foreach ( $posts as $post ) {
			$length++;
			$content = strip_tags( apply_filters( 'the_content', $post->post_content ) );
			$char_length = mb_strlen( $content, 'utf-8' );
			$total += $char_length;
			$table->addRow( [ $post->ID, $char_length, '-' ] );
		}
		$table->setFooters( [ sprintf( '%d posts', $length ), sprintf( 'Total: %d', $total ), sprintf( 'Average: %d', round( $total / $length ) ) ] );
		$table->display();
	}

	/**
	 * Compile post to XML
	 *
	 * ## OPTIONS
	 *
	 * : <taxonomy>
	 *   taxonomy
	 *
	 * : <term>
	 *   Term ID
	 *
	 * @synopsis <taxonomy> <term>
	 * @param array $args
	 * @param array $assoc
	 */
	public function compile( $args, $assoc ) {
		list( $taxonomy, $term_id ) = $args;
		$term = get_term_by( 'id', $term_id, $taxonomy );
		if ( ! $term ) {
			self::e( sprintf( 'failed to get term %d of %s', $term_id, $taxonomy ) );
		}
		$upload_dir = wp_upload_dir();
		$dir = $upload_dir['basedir'] . '/indesign/' . $taxonomy . '/' . $term->slug;
		if ( ! is_dir( $dir ) ) {
			mkdir( $dir, 0755, true );
		}
		if ( ! is_dir( $dir ) ) {
			self::e( sprintf( 'Directory %s missed.', $dir ) );
		}
		$posts = get_posts([
			'post_type' => 'post',
			'post_status' => 'any',
			'tax_query' => [
				[
					'taxonomy' => $taxonomy,
					'terms' => (int) $term_id,
				],
			],
			'posts_per_page' => -1,
		]);
		if ( ! $posts ) {
			self::e( 'No post found.' );
		}
		foreach ( $posts as $post ) {
			$xml = $this->to_xml( $post );
			file_put_contents( "{$dir}/post-{$post->ID}.xml", $xml );
			echo '.';
		}
		self::l( '' );
		self::s( 'Done.' );
	}

	/**
	 * Get XML for InDesign
	 *
	 * @param null|int|\WP_Post $post
	 *
	 * @return string
	 */
	protected function to_xml( $post = null ) {
		$post = get_post( $post );
		setup_postdata( $post );
		$xml = '
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<post>
<title>%1$s</title>
<author>%2$s</author>
<excerpt>
%3$s
</excerpt>
<body>
%4$s
</body>
</post>
';
		return sprintf(
			$xml,
			get_the_title( $post ),
			get_the_author_meta( 'display_name', $post->post_author ),
			get_the_excerpt( $post ),
			apply_filters( 'the_content', $post->post_content )
		);

	}
}
