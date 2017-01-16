<?php

namespace Hametuha\Cron;


use WPametu\Utility\CronBase;

class HotPost extends CronBase {

	/**
	 * スケジュール
	 * @var string
	 */
	protected $schedule = 'hourly';

	/**
	 * イベント名
	 *
	 * @var string
	 */
	protected $event = 'check_ranking_slack';

	/**
	 * つぶやく
	 */
	public function process() {
		if ( ! WP_DEBUG ) {
			$message = '';
			switch ( date_i18n( 'H' ) ) {
				case 8:
					$message = '【定期ポスト】昨日一番人気があった作品はこちらです。';
					$params = [
						'filters' => 'ga:dimension1==post',
						'dimensions' => 'ga:pageTitle,ga:pagePath',
					];
					$start = $end = date_i18n( 'Y-m-d', strtotime( 'Yesterday' ) );
					break;
				case 22:
					$message = '【定期ポスト】今日人気があったニュースはこちらです。';
					$params = [
						'filters' => 'ga:dimension1==news',
						'dimensions' => 'ga:pageTitle,ga:pagePath',
					];
					$start = $end = date_i18n( 'Y-m-d' );
					break;
				default:
					return; // Do nothing.
					break;
			}
			$results = hametuha_ga_ranking( $start, $end, $params );
			if ( ! is_wp_error( $results ) ) {
				$lines = [ $message ];
				foreach ( $results as $index => list( $title, $path, $pv ) ) {
					$lines[] = trim( current( explode( '|', $title ) ) );
					$lines[] = home_url( $path );
					break;
				}
				if ( 2 < count( $lines ) ) {
					update_twitter_status( implode( "\n", $lines ) );
				}
			} else {
				error_log( $results->get_error_message(), null );
			}
		}
	}

	/**
	 * Cron schedule time
	 *
	 * @return int|string
	 */
	public function start_at() {
		return strtotime( date_i18n( 'Y-m-d H:00:00', current_time( 'timestamp' ), true ) );
	}
}
