<?php

use Hametuha\Admin\Gabstract;

class HametuhaReaderRegion extends Gabstract {


	/**
	 * Action
	 */
	const ACTION = 'hametuha_ga_reader_region';

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
			'max-results' => 2000,
			'dimensions'  => 'ga:region, ga:regionId',
			'filters'     => sprintf( 'ga:dimension2==%d;ga:country==Japan', get_current_user_id() ),
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
				'region'          => 'JP',
				'displayMode'     => 'markers',
				'backgroundColor' => '#fff',
			],
			'data'    => [
				[ '地域', 'PV' ],
			],
		];
		foreach ( $result as $row ) {
			list( $region, $id, $pv ) = $row;
			$json['data'][]           = [
				$region,
				intval( $pv ),
			];
		}
		return $json;
	}

}
