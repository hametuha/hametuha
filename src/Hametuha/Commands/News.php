<?php


namespace Hametuha\Commands;

use WPametu\Utility\Command;

/**
 * News command
 *
 * @package Hametuha\Commands
 */
class News extends Command {

	const COMMAND_NAME = 'hamenew';

	/**
	 * Add date for news
	 *
	 * ## OPTIONS
	 *
	 * No option.
	 *
	 * ## EXAMPLES
	 *
	 *     wp hamenew fill_date
	 *
	 * @param array array $args
	 * @param array array $assoc_args
	 */
	public function fill_date( $args, $assoc_args ) {
		/** @var \wpdb $wpdb */
		global $wpdb;
		$query = <<<SQL
			SELECT ID, post_date FROM {$wpdb->posts}
			WHERE post_type = 'news'
			  AND post_status = 'publish'
			  AND ID NOT IN (
			  	SELECT post_id FROM {$wpdb->postmeta}
			  	WHERE meta_key = '_news_published'
			  	  AND meta_value != ''
			  )
SQL;
		$done = 0;
		foreach ( $wpdb->get_results( $query ) as $post ) {
			update_post_meta( $post->ID, '_news_published', $post->post_date );
			$done++;
			echo '.';
		}
		echo PHP_EOL;

		self::s( sprintf( '%d news date were updated!', $done ) );

	}
}
