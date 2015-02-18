<?php

use Hametuha\Admin\Gabstract;

/**
 * Get author's PV
 *
 */
class HametuhaUserPvs extends Gabstract
{

	/**
	 * Action
	 */
	const ACTION = 'hametuha_ga_pv';

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
		$date_metrics = $this->properMetrics();
		return [
			'dimensions' => $date_metrics,
			'filters' => sprintf('ga:dimension2==%d', get_current_user_id()),
			'sort' => $date_metrics,
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

		$metrics = $this->properMetrics();

		$json = [
			'options' => [
				'seriesType' => 'area',
				'series' => [
					'1' => [
						'type' => 'line'
					],
				],
				'legend' => [
					'position' => 'none',
				],
				'backgroundColor' => '#f1f1f1',
				'curveType' => 'functions',
				'animation' => [
					'duration' => 300,
		            'easing' => 'out',
				],
	            'vAxis' =>  [
		            'minValue' =>  0
				],
				'colors' => [
					'#0074A2', // Blue
					'#eb6b47' // Red
				]
			],
			'data' => [
				'cols' => [
					[
						'label' => '経過月',
						'id' => 'date',
						'type' => 'string',
					],
					[
						'label' => 'ページビュー',
						'id' => 'pv',
						'type' => 'number',
						'pattern' => '##.#pv',
					],
					[
						'label' => null,
						'type' => 'string',
						'role' => 'annotation',
					],
					[
						'label' => null,
						'type' => 'string',
						'role' => 'annotationText',
					],
					[
						'label' => '平均',
						'id' => 'avg',
						'type' => 'number',
						'pattern' => '##.#pv',
					]
				],
				'rows' => []
			],
		];
		$avg = 0;
		foreach( $result as $row ){
			$avg += intval($row[1]);
		}
		$avg = $avg / count($result);
		foreach( $result as $row ){
			list($date, $pv) = $row;
			if( $pv > 2 * $avg ){
				$annotation = [
					'v' => '★'
				];
				$annotation_text = [
					'v' => '平均の倍以上！'
				];
			}else{
				$annotation = $annotation_text = null;
			}
			$json['data']['rows'][] = [
				'c' => [
					[
						'v' => $date,
						'f' => $this->properLabel($date, $metrics),
					],
					[
						'v' => intval($pv),
						'f' => sprintf('%sPV', number_format_i18n($pv)),
					],
					$annotation,
					$annotation_text,
					[
						'v' => $avg,
						'f' => sprintf('%sPV', number_format_i18n($avg)),
					]
				],
			];
		}
		return $json;
	}


}
