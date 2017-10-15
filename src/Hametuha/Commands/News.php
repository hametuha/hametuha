<?php


namespace Hametuha\Commands;

use AFB\Admin\Table;
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
		if ( ! $posts ) {
			self::e( 'No results found.' );
		}
		$table = new \cli\Table();
		$table->setHeaders( [ '#', 'ID', 'PV', 'Author', 'Date', 'Title' ] );
		$index = 0;
		$table->setRows( array_map( function( $row ) use ( &$index ) {
			$index++;
			list( $post_id, $pv ) = $row;
			$post = get_post( $post_id );
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
			$start_index = 1;
			$per_page = 200;
			$args = [
				'max-results' => $per_page,
				'dimensions' => 'ga:pagePath',
				'filters' => 'ga:dimension1==news',
				'sort' => '-ga:pageviews',
			];
			if ( $author ) {
				$args['filters'] .= ',ga:dimension2=='.$author;
			}
			$rows = [];
			while ( true ) {
				$loop_arg = array_merge( $args, [
					'start-index' => $start_index,
				] );
				$result = $google->ga->data_ga->get( 'ga:' . $google->ga_profile['view'], $start_date, $end_date, 'ga:pageviews', $loop_arg );
				if ( ! $result || ! ( 0 < ( $count = count( $result->rows ) ) ) ) {
					break;
				}
				foreach ( $result->rows as $row ) {
					list( $path, $pv ) = $row;
					$url     = home_url( $path );
					if ( ! preg_match( '#/news/article/(\d+)/?#', $path, $matches ) ) {
						continue;
					}
					$post_id = $matches[1];
					if ( ! is_numeric( $post_id ) || ! get_post( $post_id ) ) {
						continue;
					}
					if ( ! isset( $rows[ $post_id ] ) ) {
						$rows[ $post_id ] = 0;
					}
					$rows[ $post_id ] += $pv;
				}
				// Check if more results.
				if ( $count >= $per_page ) {
					$start_index += $per_page;
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
		] as $old_key => $new_key  ) {
			$replaced = $wpdb->query( $wpdb->prepare( $query, $new_key, $old_key ) );
			self::l( sprintf( 'Change %s to %s: %d', $old_key, $new_key, $replaced ) );
		}
		self::s( 'Changing key is finished. Please flush post cache.' );
	}

	/**
	 * Fix instant article contents.
	 */
	public function fix_instant_articles() {
		$count = 0;
		$errors = [];
		if ( ! function_exists( 'gianism_fb_update_instant_article' ) ) {
			\WP_CLI::error( 'gianism_fb_update_instant_articleがありません。' );
		}
		if ( 'https://hametuha.com' !== home_url() ) {
			\WP_CLI::error( '本番環境でしか実行できません。' );
		}
		add_filter( 'the_content', '_fb_instant_content', 11 );
		foreach ( get_posts( [
			'post_type' => 'news',
			'post_status' => 'publish',
			'posts_per_page' => -1,
		] ) as $post ) {
			$count++;
			setup_postdata( $post );
			ob_start();
			get_template_part( 'templates/news/instant-article' );
			$html = ob_get_contents();
			ob_end_clean();
			$import_status = gianism_fb_update_instant_article( $html, true );
			if ( is_wp_error( $import_status ) ) {
				$errors[ $post->ID ] = $import_status->get_error_message();
				echo 'x';
			} else {
				update_post_meta( $post->ID, '_fb_instant_article_status', $import_status );
				echo '.';
			}
		}
		foreach ( $errors as $id => $message ) {
			\WP_CLI::warning( sprintf( '%d: %s', $id, $message ) );
		}
		\WP_CLI::success( sprintf( '%d件を更新しました。', $count ) );
	}

	/**
	 * Check Instant article status
	 */
	public function get_status() {
		$api = gianism_fb_page_api();
		if ( is_wp_error( $api ) ) {
			\WP_CLI::error( $api->get_error_message() );
		}
		$table = new \cli\Table();
		$table->setHeaders( [ 'ID', 'Status', 'Errors' ] );
		try {
			foreach ( get_posts( [
				'post_type' => 'news',
				'post_status' => 'publish',
				'posts_per_page' => -1,
				'meta_query' => [
					[
						'key' => '_fb_instant_article_status',
						'value' => '',
						'compare' => '!=',
					]
				],
			] ) as $post ) {
				$status_id = get_post_meta( $post->ID, '_fb_instant_article_status', true );
				$result = $api->get( add_query_arg( [
					'access_token' => $api->getDefaultAccessToken()->getValue(),
					'fields' => 'status,errors',
				], $status_id ) )->getGraphNode();
				$status = $result->getField( 'status' );
				if ( 'SUCCESS' == $status ) {
					echo '.';
				} else {
					$table->addRow( [
						$post->ID,
						$status,
						implode( "\n", array_map( function ( $error ) {
							return $error->getField( 'message' );
						}, $result->getField( 'errors' ) ) ),
					] );
					echo 'x';
				}
			}
			\WP_CLI::line( '' );
			$table->display();
		} catch ( \Exception $e ) {
			\WP_CLI::error( $e->getMessage() );
		}
	}
}
