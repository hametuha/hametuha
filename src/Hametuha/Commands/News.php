<?php


namespace Hametuha\Commands;

use Gianism\Plugins\Analytics;
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
		$author = isset( $assoc['author'] ) ? $assoc['author'] : 0;
		$posts = $this->get_pv( $start_date, $end_date, $author );
		$table = new \cli\Table();
		$table->setHeaders( [ 'Title', 'Author', 'Date', 'PV' ] );
		$table->setRows( array_map( function($row){
			$post = get_post( $row[0] );
			return [
				get_the_title( $post ),
			    get_the_author_meta( 'display_name', $post->post_author ),
			    get_the_time( 'Y.m.d', $post ),
			    $row[1],
			];
		}, $posts ) );
		$table->display();
	}

	/**
	 * ニュースのランキングを取得する
	 *
	 * @synopsis --from=<from> [--to=<to>] [--author=<author>]
	 * @param array $args
	 * @param array $assoc
	 */
	public function update_pv( $args, $assoc ) {
		$start_date = $assoc['from'];
		$end_date   = isset( $assoc['to'] ) ? $assoc['to'] : date_i18n( 'Y-m-d' );
		$author = isset( $assoc['author'] ) ? $assoc['author'] : 0;
		$posts = $this->get_pv( $start_date, $end_date, $author );
		$done = 0;
		foreach ( $posts as $post ) {
			update_post_meta( $post[0], '_current_pv', $post[1] );
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
			$google = Analytics::get_instance();
			if ( ! $google || ! $google->ga_profile['view'] ) {
				throw new \Exception( 'Google Analytics is not connected.', 500 );
			}
			$args = [
				'max-results' => 200,
				'dimensions' => 'ga:pagePath',
				'filters' => 'ga:dimension1==news',
				'sort' => '-ga:pageviews',
			];
			if ( $author ) {
				$args['filters'] .= ',ga:dimension2=='.$author;
			}
			$result = $google->ga->data_ga->get( 'ga:' . $google->ga_profile['view'], $start_date, $end_date, 'ga:pageviews', $args );
			if ( $result && ( 0 < count( $result->rows ) ) ) {
				return array_filter(array_map( function( $row ){
					list( $path, $pv ) = $row;
					$url = home_url( $path );
					$post_id = preg_replace( '#/news/article/([0-9]+)/.*?#', '$1', $path );
					if ( ! $post_id || ! get_post( $post_id ) ) {
						return false;
					}
					$row[0] = $post_id;
					return $row;
				}, $result->rows ));
			} else {
				return [];
			}
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
		] as $old_key => $new_key  ) {
			$replaced = $wpdb->query( $wpdb->prepare( $query, $new_key, $old_key ) );
			self::l( sprintf( 'Change %s to %s: %d', $old_key, $new_key, $replaced ) );
		}
		self::s( 'Changing key is finished. Please flush post cache.' );
	}
}
