<?php

namespace Hametuha\Cron;


use WPametu\Utility\CronBase;

/**
 * キャンペーンの締め切りを通知する
 */
class CampaignLimit extends CronBase {

	/**
	 * スケジュール
	 * @var string
	 */
	protected $schedule = 'daily';

	/**
	 * イベント名
	 *
	 * @var string
	 */
	protected $event = 'notify_campaign_limit';

	/**
	 * 月曜日に、来週〆切を迎えるキャンペーンを告知
	 *
	 * @return void
	 */
	public function process() {
		// デバッグモードなので実行しない。
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			return;
		}
		// 月曜日だけ実行
		if ( '1' !== date_i18n( 'w' ) ) {
			return;
		}
		// 来週に締め切りを迎えるキャンペーンを取得
		$tz = new \DateTimeZone( wp_timezone_string() );
		$start_line = new \DateTime( 'now', $tz );
		$start_line->add( new \DateInterval( 'P1D' ) );
		$start_date = $start_line->format( 'Y-m-d' );
		$deadline = new \DateTime( 'now', $tz );
		$deadline->add( new \DateInterval( 'P7D' ) );
		$end_date = $deadline->format( 'Y-m-d' );
		$campaigns = hametuha_get_nearing_deadline_campaigns( $start_date, $end_date, 3 );
		if ( empty( $campaigns ) ) {
			// 該当する応募がない。
			return;
		}
		$message = $this->campaign_message( $campaigns );
		// twitterに通知を試みる
		if ( function_exists( 'gianism_update_twitter_status' ) ) {
			gianism_update_twitter_status( $message );
		}
		// Slackに通知を試みる
		hametuha_slack( $message );
	}

	/**
	 * SNSにシェアする用途の文言を生成する。
	 *
	 * @param \WP_Term[] $campaigns キャンペーンの配列。
	 * @return string
	 */
	protected function campaign_message( $campaigns ) {
		$message = [ '破滅派でまもなく〆切になる公募があります。今週がんばりましょう。' ];
		foreach ( $campaigns as $campaign ) {
			$limit     = get_term_meta( $campaign->term_id, '_campaign_limit', true );
			$limit     = mysql2date( 'Y年m月d日（D）', $limit );
			$message[] = sprintf( "%s　%s〆切\n%s", $campaign->name, $limit, get_term_link( $campaign ) );
		}
		return implode( "\n\n", $message );
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
