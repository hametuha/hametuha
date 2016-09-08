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
			$attachments = [];
			$channel = '#general';
			switch ( date_i18n( 'H' ) ) {
				case 8:
					$message = 'ここ3日で人気があったページのトップ20件です。';
					$params = [
						'filters' => 'ga:dimension1=~(post|news)',
						'dimensions' => 'ga:pageTitle,ga:pagePath',
					    'max-results' => 20,
					];
					$start = date_i18n( 'Y-m-d', strtotime( '3 days ago' ) );
					$end   = date_i18n( 'Y-m-d', strtotime( 'Yesterday' ) );
					break;
				case 18:
					$message = '昨日人気があった投稿の上位10件です。';
					$params = [
						'filters' => 'ga:dimension1==post',
						'dimensions' => 'ga:pageTitle,ga:pagePath',
					];
					$start = $end = date_i18n( 'Y-m-d', strtotime( 'Yesterday' ) );
					break;
				case 21:
					$message = '今日人気があったニュースです。';
					$params = [
						'filters' => 'ga:dimension1==news',
						'dimensions' => 'ga:pageTitle,ga:pagePath',
					];
					$start = $end = date_i18n( 'Y-m-d' );
					$channel = '#news';
					break;
				default:
					return; // Do nothing.
					break;
			}
			$results = hametuha_ga_ranking( $start, $end, $params );
			if ( ! is_wp_error( $results ) ) {
				foreach ( $results as $index => list( $title, $path, $pv ) ) {
					$title         = trim( current( explode( '|', $title ) ) );
					$type = ( false !== strpos( $path, 'news/' ) ) ? 'ニュース' : '投稿';
					$url           = home_url( $path );
					$attachments[] = [
						'title'      => $title,
						'title_link' => $url,
						'text'       => sprintf( '%d位 %s PV（%s）', ( $index + 1 ), number_format( $pv ), $type ),
						'fallback'   => $title,
					];
				}
				hametuha_slack( "@here {$message}", $attachments, $channel );
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
