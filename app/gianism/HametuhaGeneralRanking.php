<?php

use \Gianism\Cron\Daily;

/**
 * Google Analyticsからデータを取得する
 *
 */
class HametuhaGeneralRanking extends Daily {

	const CATEGORY = 'general';

	/**
	 * Google Analyticsからデータを取得する
	 *
	 * @return array
	 */
	public function get_results() {
		// 48時間経たないとデータは取れないので、
		// 3日前
		$three_days_ago = date( 'Y-m-d', strtotime( '-3days' ) );
		$result         = $this->fetch( $three_days_ago, $three_days_ago, 'ga:pageviews', array(
			'max-results' => 200,
			'dimensions'  => 'ga:pagePath',
			'filters'     => 'ga:dimension1==post',
			'sort'        => '-ga:pageviews',
		) );
		foreach ( $result as $key => $row ) {
			// 日付情報を加える
			$result[ $key ][] = $three_days_ago;
			// URLを投稿IDに変換
			$result[ $key ][0] = preg_replace( '/[^0-9]/u', '', $result[ $key ][0] );
		}

		return $result;
	}

	/**
	 * 結果の行をそれぞれ保存
	 *
	 * @param $result
	 *
	 * @return void
	 */
	protected function parse_row( $result ) {
		list( $path, $pv, $date ) = $result;
		$this->save( $date, preg_replace( '/[^0-9]/', '', $path ), $pv );
	}
}