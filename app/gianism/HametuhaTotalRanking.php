<?php

use Gianism\Cron\Daily;

/**
 * Get total PV and save them as post_meta
 *
 * @deprecated 8.0.0
 * @package Hametuha\gianism
 */
class HametuhaTotalRanking extends Daily {

	const CATEGORY = 'diff';

	const SKIP_CRON = true;

	/**
	 * Start time
	 *
	 * @return int
	 */
	public function build_timestamp() {
		// Next midnight.
		$now = current_time( 'timestamp' );
		if ( (int) date_i18n( 'H' ) < 3 ) {
			$now += 60 * 60 * 24;
		}

		return (int) get_gmt_from_date( date_i18n( 'Y-m-d', $now ) . '03:00:00', 'U' );
	}

	/**
	 * Do cron
	 */
	public function do_cron() {
		if ( ! self::SKIP_CRON && $this->ga ) {
			$start_index = 1;
			$did         = [];
			while ( $result = $this->retrieve( $start_index ) ) {
				foreach ( $result as $row ) {
					list( $post_id, $pv ) = $row;
					$post_id              = trim( $post_id, '/' );
					if ( false === array_search( $post_id, $did ) ) {
						$did[]   = $post_id;
						$old     = (int) get_post_meta( $post_id, '_old_pv', true );
						$current = (int) get_post_meta( $post_id, '_current_pv', true );
						$latest  = $old + $pv;
						// Save current PV
						update_post_meta( $post_id, '_current_pv', $latest );
						// Record diff
						$this->save( date_i18n( 'Y-m-d' ), $post_id, $latest - $current );
					}
					// Make offset
					$start_index ++;
				}
			}
		}
	}

	/**
	 * Get data
	 *
	 * @param int $offset
	 *
	 * @return array
	 */
	protected function retrieve( $offset ) {
		// TODO: 新しいAPIに移行する
		$start_date = '2014-11-02';
		$end_date   = date_i18n( 'Y-m-d', current_time( 'timestamp' ) - 60 * 60 * 24 * 3 ); // 2 days ago
		return $this->fetch( $start_date, $end_date, 'ga:pageviews', array(
			'dimensions'  => 'ga:pagePathLevel2',
			'sort'        => '-ga:pageviews',
			'max-results' => 200,
			'filters'     => 'ga:dimension1==post;ga:pagePath!@preview',
			'start-index' => $offset,
		) );
	}


	/**
	 * Get result
	 *
	 * @return array
	 */
	public function get_results() {
		// Do nothing.
	}


	/**
	 * Applied for each result
	 *
	 * @param $result
	 */
	protected function parse_row( $result ) {
		// Nothing to do
	}


}
