<?php

use Hametuha\Admin\Gabstract;

class HametuhaReaderSegment extends Gabstract {


	/**
	 * Action
	 */
	const ACTION = 'hametuha_ga_reader_segment';

	/**
	 * Nonce Action
	 */
	const NONCE_ACTION = 'hametuha_ga';


	/**
	 * Should return metrics
	 *
	 * @see https://developers.google.com/analytics/devguides/reporting/core/dimsmets
	 * @return string CSV of metrics E.g., 'ga:visits,ga:pageviews'
	 */
	protected function get_metrics() {
		return 'ga:pageviews';
	}

	/**
	 * Should return parameters
	 *
	 * @see self::fetch
	 * @return array
	 */
	protected function get_params() {
		return [
			'max-results' => 10,
			'dimensions'  => 'ga:userGender, ga:userAgeBracket',
			'filters'     => sprintf( 'ga:dimension2==%d', get_current_user_id() ),
			'sort'        => '-ga:pageviews',
		];
	}


	/**
	 * Change result
	 *
	 * @param array $result
	 *
	 * @return array
	 */
	protected function parse_result( array $result ) {
		$json = [
			'options' => [
				'legend'          => [
					'position' => 'none',
				],
				'backgroundColor' => '#fff',
				'colors'          => [],
			],
			'data'    => [
				[ '属性', '割合' ],
			],
		];
		foreach ( $result as $row ) {
			list( $sex, $age, $pv )      = $row;
			$female                      = 'female' == $sex;
			$hue                         = $female ? 5 : 140;
			$age                         = explode( '-', $age );
			$saturation                  = 255 - ( $age[0] * 2 );
			$lightness                   = 150 - $age[0] * 2;
			$json['options']['colors'][] = $this->hsl2hex( [
				$hue / 255,
				$saturation / 255,
				round( $lightness / 255, 2 ),
			] );
			if ( ! isset( $age[1] ) ) {
				$age[1] = '';
			}
			$json['data'][] = [
				sprintf( '%s（%d〜%s歳）', ( $female ? '女性' : '男性' ), $age[0], $age[1] ),
				intval( $pv ),
			];
		}

		return $json;
	}

}
