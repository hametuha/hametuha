<?php

namespace Hametuha\Notifications\Emails;


use Hametuha\Hamail\Pattern\TransactionalEmail;


/**
 * 報酬の振込処理を行ったことを通知する
 *
 * 管理画面の請求一覧で「支払い済みにする」を実行したときに送られる。
 *
 * @package Hametuha\Notifications\Emails
 */
class RevenueTransferred extends TransactionalEmail {

	/**
	 * Return mail body.
	 *
	 * @return string
	 */
	protected function get_body() {
		return <<<HTML

-name- さん

いつも破滅派をご利用いただきありがとうございます。
-date- に、登録いただいている口座への振込処理を行いました。

土日祝日や金融機関の営業時間によっては、実際の入金が数日後になる場合があります。
振込額の内訳は、入金履歴からご確認いただけます。

-url-

HTML;
	}

	/**
	 * Returns title.
	 *
	 * @return string
	 */
	protected function get_subject() {
		return '破滅派 報酬の振込処理を行いました';
	}

	/**
	 * Register hooks here.
	 */
	public static function register() {
		add_action( 'sharee_revenue_transfered', function ( $user_ids ) {
			$recipients = [];
			foreach ( (array) $user_ids as $user_id ) {
				$recipients[ (int) $user_id ] = [
					'date' => date_i18n( 'Y年n月j日' ),
					'url'  => home_url( 'dashboard/sales/payments/' ),
				];
			}
			if ( $recipients ) {
				static::exec( $recipients );
			}
		} );
	}
}
