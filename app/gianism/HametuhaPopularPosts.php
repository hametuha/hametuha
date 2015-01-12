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
		$data = [
			'labels' => [],
			'datasets' => [],
		];
		$dataset = [
			'label' => "人気の投稿",
			'fillColor' => "rgba(220,220,220,0.2)",
			'strokeColor' => "rgba(220,220,220,1)",
			'pointColor' => "rgba(220,220,220,1)",
			'pointStrokeColor' => "#fff",
			'pointHighlightFill' => "#fff",
			'pointHighlightStroke' => "rgba(220,220,220,1)",
			'data' => [],
		];
		foreach( $result as $row ){
			list($title, $pv) = $row;
			$data['labels'][] = trim(explode('|', $title)[0]);
			$dataset['data'][] = intval($pv);
		}
		$data['datasets'][] = $dataset;
		return $data;
	}
}