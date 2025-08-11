<?php

namespace Hametuha\Cron;


use WPametu\Utility\CronBase;

/**
 * 人気の記事についてつぶやく
 */
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
	 * 昨日の日付を返す
	 *
	 * @parma int    $offset 指定する日付
	 * @parma string $format フォーマット
	 * @return string
	 */
	protected function get_yesterday( $offset = -1, $format = 'Y-m-d' ) {
		$date = new \DateTime( 'now', new \DateTimeZone( wp_timezone_string() ) );
		if ( $offset < 0 ) {
			$date->sub( new \DateInterval( 'P1D' ) );
		} else {
			$date->add( new \DateInterval( 'P1D' ) );
		}
		return $date->format( $format );
	}

	/**
	 * つぶやく
	 */
	public function process() {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			// デバッグモードでは実行しない。
			return;
		}
		// 8時と22時にしか実行しない。
		if ( ! in_array( date_i18n( 'H' ), [ '08', '22' ], true ) ) {
			return;
		}
		$yesterday = $this->get_yesterday();
		$start     = $yesterday;
		$end       = $yesterday;
		$channel   = '#general';
		$post_type = 'post';
		switch ( date_i18n( 'H' ) ) {
			case '08':
				$message = '【定期ポスト】昨日一番人気があった作品はこちらです。';
				break;
			case '22':
				$message = '【定期ポスト】昨日よく読まれたニュースはこちらです。';
				$post_type = 'news';
				$channel   = '#news';
				break;
			default:
				return; // Do nothing.
		}
		$results = hametuha_hot_posts( $start, $end, $post_type, 3 );
		if ( is_wp_error( $results ) ) {
			error_log( $results->get_error_message() );
		} elseif ( empty( $results ) ) {
			return;
		} else {
			$lines = [ $message ];
			foreach ( $results as list( $title, $path, $pv ) ) {
				list( $post_title ) = explode( '|', $title );
				$lines[]            = trim( $post_title ) . "\n" . home_url( $path );
				break;
			}
			$message = implode( "\n\n", $lines );
			// twitterにつぶやく
			if ( function_exists( 'gianism_update_twitter_status' ) ) {
				gianism_update_twitter_status( $message );
			}
			// Slackに投稿
			hametuha_slack( $message, [], $channel );
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
