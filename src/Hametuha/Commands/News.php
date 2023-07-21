<?php


namespace Hametuha\Commands;

use Hametuha\Service\GoogleAnalyticsDataAccessor;
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
		$done  = 0;
		foreach ( $wpdb->get_results( $query ) as $post ) {
			update_post_meta( $post->ID, '_news_published', $post->post_date );
			$done++;
			echo '.';
		}
		echo PHP_EOL;

		self::s( sprintf( '%d news date were updated!', $done ) );

	}


	/**
	 * ニュースのランキングを取得する
	 *
	 * @synopsis --from=<from> [--to=<to>] [--author=<author>]
	 * @param array $args
	 * @param array $assoc
	 */
	public function show_pv( $args, $assoc ) {
		$start_date = $assoc['from'];
		$end_date   = isset( $assoc['to'] ) ? $assoc['to'] : date_i18n( 'Y-m-d' );
		$author     = isset( $assoc['author'] ) ? $assoc['author'] : 0;
		$posts      = $this->get_pv( $start_date, $end_date, $author );
		if ( ! $posts ) {
			self::e( 'No results found.' );
		}
		$table = new \cli\Table();
		$table->setHeaders( [ '#', 'ID', 'PV', 'Author', 'Date', 'Title' ] );
		$index = 0;
		$table->setRows( array_map( function( $row ) use ( &$index ) {
			$index++;
			list( $post_id, $pv ) = $row;
			$post                 = get_post( $post_id );
			return [
				$index,
				$post_id,
				$pv,
				get_the_author_meta( 'user_login', $post->post_author ),
				get_the_time( 'Y.m.d', $post ),
				get_the_title( $post ),
			];
		}, $posts ) );
		$table->display();

		self::s( sprintf( '%s News', number_format( count( $posts ) ) ) );
	}

	/**
	 * ニュースのランキングを取得する
	 *
	 * @synopsis [--from=<from>] [--to=<to>] [--author=<author>]
	 * @param array $args
	 * @param array $assoc
	 */
	public function update_pv( $args, $assoc ) {
		$yesterday  = new \DateTime( 'yesterday', wp_timezone() );
		$start_date = $assoc['from'] ?? $yesterday->format( 'Y-m-d' );
		$end_date   = isset( $assoc['to'] ) ? $assoc['to'] : date_i18n( 'Y-m-d' );
		$author     = isset( $assoc['author'] ) ? $assoc['author'] : 0;
		$posts      = $this->get_pv( $start_date, $end_date, $author );
		$done       = 0;
		foreach ( $posts as list( $post_id, $pv ) ) {
			$current = (int) get_post_meta( $post_id, '_current_pv', true );
			update_post_meta( $post_id, '_current_pv', $current + $pv );
			$done++;
			echo '.';
		}
		echo PHP_EOL;
		self::s( sprintf( '%d news were updated.', $done ) );
	}

	/**
	 * Get PV
	 *
	 * @param string $start_date
	 * @param string $end_date
	 * @param int $author
	 *
	 * @return array
	 */
	protected function get_pv( $start_date, $end_date, $author = 0 ) {
		try {
			$offset      = 0;
			$per_page    = 1000;
			$args     = [
				'start'     => $start_date,
				'end'       => $end_date,
				'post_type' => 'news',
				'author'    => $author ?: '',
				'limit'     => $per_page,
			];
			$rows = [];
			while ( true ) {
				$loop_arg = array_merge( $args, [
					'offset' => $offset,
				] );
				$result = GoogleAnalyticsDataAccessor::get_instance()->popular_posts( $loop_arg );
				if ( ! $result || is_wp_error( $result ) ) {
					break;
				}

				foreach ( $result as $row ) {
					list( $path, $post_type, $author, $category, $pv ) = $row;
					$post_id = url_to_postid( home_url( $path ) );
					if ( ! $post_id ) {
						continue 1;
					}
					if ( ! isset( $rows[ $post_id ] ) ) {
						$rows[ $post_id ] = 0;
					}
					$rows[ $post_id ] += $pv;
				}
				// Check if more results.
				if ( count( $result ) >= $per_page ) {
					$offset += $per_page;
				} else {
					break;
				}
			}
			arsort( $rows );
			$result_rows = [];
			foreach ( $rows as $id => $pv ) {
				$result_rows[] = [ $id, $pv ];
			}
			return $result_rows;
		} catch ( \Exception $e ) {
			self::e( $e->getMessage() );
		}
	}

	/**
	 * Change meta
	 */
	public function fix_event() {
		global $wpdb;
		$query = <<<SQL
			UPDATE {$wpdb->postmeta}
			SET meta_key = %s
			WHERE meta_key = %s
SQL;
		foreach ( [
			'_hametuha_announcement_place'    => '_event_title',
			'_hametuha_announcement_building' => '_event_bld',
			'_hametuha_announcement_address'  => '_event_address',
			'_hametuha_announcement_notice'   => '_event_desc',
			'_hametuha_announcement_point'    => '_event_point',
			'_lwp_event_start'                => '_event_start',
			'_lwp_event_end'                  => '_event_end',
		] as $old_key => $new_key ) {
			$replaced = $wpdb->query( $wpdb->prepare( $query, $new_key, $old_key ) );
			self::l( sprintf( 'Change %s to %s: %d', $old_key, $new_key, $replaced ) );
		}
		self::s( 'Changing key is finished. Please flush post cache.' );
	}

}
