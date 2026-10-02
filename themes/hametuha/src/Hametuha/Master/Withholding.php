<?php

namespace Hametuha\Master;


use Hametuha\Sharee\Master\Address;
use Hametuha\Sharee\Models\RevenueModel;

/**
 * 源泉徴収のある支払いの一覧
 *
 * 報酬支払一覧表（スプレッドシート）の A〜J 列と同じ並びで行を返す。
 * 管理画面の TSV/CSV ダウンロードと REST API で共用する。
 *
 * @package Hametuha\Master
 */
class Withholding {

	/**
	 * 摘要
	 */
	const LABEL = '原稿料ほか';

	/**
	 * 指定した月に支払い済みにした、源泉徴収のある支払いをユーザーごとに合算して返す
	 *
	 * 同じ月に同じユーザーへ複数回支払った場合、日付はその月の最後の支払日にする。
	 *
	 * @param int $year  年
	 * @param int $month 月。0 なら年全体
	 * @return array[]
	 */
	public static function get_records( $year, $month = 0 ) {
		global $wpdb;
		$year  = (int) $year;
		$month = (int) $month;
		$table = RevenueModel::get_instance()->table;
		if ( $month ) {
			$where = $wpdb->prepare( 'EXTRACT(YEAR_MONTH FROM fixed) = %d', sprintf( '%04d%02d', $year, $month ) );
		} else {
			// 年全体のときも、シートの行と揃えるため月ごとに分けて合算する。
			$where = $wpdb->prepare( 'EXTRACT(YEAR FROM fixed) = %d', $year );
		}
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_results( <<<SQL
			SELECT
				object_id,
				EXTRACT(YEAR_MONTH FROM fixed) AS period,
				MAX(fixed) AS fixed,
				SUM( price * unit ) AS before_tax,
				SUM( deducting ) AS deducting,
				SUM( tax ) AS tax,
				SUM( total ) AS total
			FROM {$table}
			WHERE {$where}
			  AND status = 1
			  AND deducting > 0
			GROUP BY object_id, period
			ORDER BY period ASC, fixed ASC, object_id ASC
SQL
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return array_map( [ static::class, 'to_record' ], $rows );
	}

	/**
	 * DB の行をスプレッドシートの1行に変換する
	 *
	 * @param \stdClass $row 行
	 * @return array
	 */
	protected static function to_record( $row ) {
		$user_id = (int) $row->object_id;
		$address = new Address( $user_id );
		return [
			'key'          => sprintf( 'wp:%s-%d', $row->period, $user_id ),
			'user_id'      => $user_id,
			'month'        => (int) mysql2date( 'n', $row->fixed ),
			'day'          => (int) mysql2date( 'j', $row->fixed ),
			'payee'        => (string) $address->get_value( 'name' ),
			'label'        => self::LABEL,
			'before_tax'   => (int) round( $row->before_tax ),
			'deducting'    => (int) round( $row->deducting ),
			'tax'          => (int) round( $row->tax ),
			'total'        => (int) round( $row->total ),
			'address'      => $address->format_line(),
			'display_name' => (string) get_the_author_meta( 'display_name', $user_id ),
		];
	}

	/**
	 * 管理画面ダウンロード用に、スプレッドシートの A〜J 列の順で値を並べる
	 *
	 * @param array $record get_records() の要素
	 * @return array
	 */
	public static function to_columns( $record ) {
		return [
			sprintf( '%02d', $record['month'] ),
			sprintf( '%02d', $record['day'] ),
			$record['payee'],
			$record['label'],
			$record['before_tax'],
			$record['deducting'],
			$record['tax'],
			$record['total'],
			$record['address'],
			$record['display_name'],
		];
	}
}
