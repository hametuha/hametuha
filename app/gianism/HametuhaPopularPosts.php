<?php

use Hametuha\Admin\Gabstract;

class HametuhaPopularPosts extends Gabstract {


	/**
	 * Action
	 */
	const ACTION = 'hametuha_ga_popular';

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
			'dimensions'  => 'ga:pageTitle',
			'filters'     => sprintf( 'ga:dimension1==post;ga:dimension2==%d', get_current_user_id() ),
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
				'cols' => [
					[
						'label' => 'タイトル',
						'id'    => 'title',
						'type'  => 'string',
					],
					[
						'label'   => 'ページビュー',
						'id'      => 'pv',
						'type'    => 'number',
						'pattern' => '##.#pv',
					],
				],
				'rows' => [],
			],
		];
		foreach ( $result as $row ) {
			list( $title, $pv )     = $row;
			$json['data']['rows'][] = [
				'c' => [
					[
						'v' => trim( explode( '|', $title )[0] ),
					],
					[
						'v' => intval( $pv ),
						'f' => sprintf( '%sPV', number_format_i18n( $pv ) ),
					],
				],
			];
		}

		return $json;
	}
}
