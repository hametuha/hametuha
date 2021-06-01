<?php

use Hametuha\Admin\Gabstract;

class HametuhaTrafficContributor extends Gabstract {


	/**
	 * Action
	 */
	const ACTION = 'hametuha_ga_traffic_contributor';

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
			'dimensions'  => 'ga:medium',
			'filters'     => sprintf( 'ga:dimension2==%d;ga:campaign=~^share', get_current_user_id() ),
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
				'backgroundColor' => '#fff',
				'legend'          => [
					'position' => 'none',
				],
			],
			'data'    => [
				[ '名前', 'PV', [ 'role' => 'style' ] ],
			],
		];
		foreach ( $result as $row ) {
			$color                = 'blue';
			list( $user_id, $pv ) = $row;
			if ( ! $user_id ) {
				$label = 'ゲスト';
			} elseif ( '1' === $user_id ) {
				$label = '破滅派自動';
			} elseif ( get_current_user_id() == $user_id ) {
				$label = 'あなた';
				$color = 'red';
			} else {
				$label = get_the_author_meta( 'display_name', $user_id );
			}
			$json['data'][] = [
				$label,
				intval( $pv ),
				$color,
			];
		}

		return $json;
	}

}
