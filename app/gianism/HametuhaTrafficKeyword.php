<?php

use Hametuha\Admin\Gabstract;

class HametuhaTrafficKeyword extends Gabstract {


	/**
	 * Action
	 */
	const ACTION = 'hametuha_ga_traffic_keyword';

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
			'max-results' => 1000,
			'dimensions'  => 'ga:pageTitle, ga:keyword',
			'filters'     => sprintf( 'ga:dimension1!=page;ga:dimension2==%d;ga:keyword!~not (set|provided);ga:pageviews>0', get_current_user_id() ),
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
				'width'           => '100%',
				'page'            => 'enable',
				'pageSize'        => 12,
			],
			'data'    => [
				[ 'キーワード', '作品', 'PV' ],
			],
		];
		foreach ( $result as $row ) {
			list( $page_title, $keyword, $pv ) = $row;
			list( $title )                     = array_map( 'trim', preg_split( '/[|｜]/u', $page_title ) );
			$json['data'][]                    = [
				$keyword,
				$title,
				intval( $pv ),
			];
		}

		return $json;
	}

}
