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
		$data = [
			'labels' => [],
			'datasets' => [],
		];
		$dataset = [
			'label' => "日時別ページビュー",
			'fillColor' => "rgba(220,220,220,0.2)",
			'strokeColor' => "rgba(220,220,220,1)",
			'pointColor' => "rgba(220,220,220,1)",
			'pointStrokeColor' => "#fff",
			'pointHighlightFill' => "#fff",
			'pointHighlightStroke' => "rgba(220,220,220,1)",
			'data' => [],
		];
		$metrics = $this->properMetrics();
		foreach( $result as $row ){
			list($date, $pv) = $row;
			$data['labels'][] = $this->properLabel($date, $metrics);
			$dataset['data'][] = intval($pv);
		}
		$data['datasets'][] = $dataset;
		return $data;
	}


}
