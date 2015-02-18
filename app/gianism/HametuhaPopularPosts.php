<?php

use Hametuha\Admin\Gabstract;

class HametuhaPopularPosts extends Gabstract
{


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
			'max-results' => 10,
			'dimensions' => 'ga:pageTitle',
			'filters' => sprintf('ga:dimension1==post;ga:dimension2==%d', get_current_user_id()),
			'sort' => '-ga:pageviews',
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
				'legend' => [
					'position' => 'none',
				],
				'backgroundColor' => '#f1f1f1',
				'animation' => [
					'duration' => 300,
					'easing' => 'out',
				],
				'bars' => 'horizontal',
				'axes' => [
					'x' => [
						'0' => [
							'side' => 'top',
							'label' => 'Percentage',
						]
					]
	            ],
				'colors' => [
					'#0074A2', // Blue
				]
			],
			'data' => [
				'cols' => [
					[
						'label' => 'タイトル',
						'id' => 'title',
						'type' => 'string',
					],
					[
						'label' => 'ページビュー',
						'id' => 'pv',
						'type' => 'number',
						'pattern' => '##.#pv',
					],
				],
				'rows' => []
			]
		];
		foreach( $result as $row ){
			list($title, $pv) = $row;
			$json['data']['rows'][] = [
				'c' => [
					[
						'v' => trim(explode('|', $title)[0]),
					],
					[
						'v' => intval($pv),
						'f' => sprintf('%sPV', number_format_i18n($pv)),
					],
				]
			];
		}
		return $json;
	}
}