<?php

use Hametuha\Admin\Gabstract;

class HametuhaTrafficSource extends Gabstract {


	/**
	 * Action
	 */
	const ACTION = 'hametuha_ga_traffic_source';

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
			'max-results' => 100,
			'dimensions'  => 'ga:channelGrouping',
			'filters'     => sprintf( 'ga:dimension2==%d', get_current_user_id() ),
			'sort'        => 'ga:channelGrouping',
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
				'pieHole'         => 0.4,
				'backgroundColor' => '#fff',
				'legend' => [
					'position' => 'top',
				],
			],
			'data'    => [
				[ '参照元', 'PV' ],
			],
		];
		foreach ( $result as $row ) {
			list( $channel, $pv ) = $row;
			switch ( strtolower( $channel ) ) {
				case '(other)':
					$label = 'その他';
					break;
				case 'social':
					$label = 'SNS';
					break;
				case 'referral':
					$label = '被リンク';
					break;
				case 'organic search':
					$label = '検索エンジン';
					break;
				case 'direct':
					$label = '直接訪問';
					break;
				case 'display':
					$label = 'ディスプレイ広告';
					break;
				default:
					$label = $channel;
					break;
			}
			$json['data'][] = [
				$label,
				intval( $pv ),
			];
		}

		return $json;
	}

}