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
}
