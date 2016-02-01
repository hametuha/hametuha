<?php

namespace Hametuha\Cron;


use WPametu\Utility\CronBase;

class KdpTweet extends CronBase
{

	/**
	 * スケジュール
	 * @var string
	 */
	protected $schedule = 'meet_everybody';

	/**
	 * イベント名
	 *
	 * @var string
	 */
	protected $event = 'tweet_kdp';

	/**
	 * つぶやく
	 */
	public function process() {
		if ( ! WP_DEBUG ) {
			update_twitter_status( $this->tweet() );
		}
	}

	/**
	 * つぶやく文字列を返す
	 *
	 * @return string
	 */
	public function tweet() {
		$string = '【定期告知】破滅派のKindle本は現在[kdp_count]冊販売中です。まだご覧になっていない方はぜひご高覧ください！　今後も続々と販売予定です。 http://hametuha.com/kdp/';
		return do_shortcode( $string );
	}

	/**
	 * Cron schedule time
	 *
	 * @return int|string
	 */
	public function start_at() {
		return strtotime( date_i18n( 'Y-m-d 15:i:s', true ) );
	}


	/**
	 * Override this if schedules required.
	 *
	 * @see http://codex.wordpress.org/wp_schedule_event
	 *
	 * @param array $schedule Schedule array.
	 *
	 * @return mixed
	 */
	public function cron_schedule( $schedule ) {
		$schedule['meet_everybody'] = [
			'interval' => 60 * 60 * (18 + 24),
			'display'  => '1日と18時間おきにつぶやくと、いろんな人の目に入る',
		];
		return $schedule;
	}


}
