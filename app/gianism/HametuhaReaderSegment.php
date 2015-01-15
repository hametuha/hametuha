<?php

use Hametuha\Admin\Gabstract;

class HametuhaReaderSegment extends Gabstract
{



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
			'dimensions' => 'ga:userGender, ga:userAgeBracket',
			'filters' => sprintf('ga:dimension2==%d', get_current_user_id()),
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
		$data = [];
		foreach( $result as $row ){
			list($sex, $age, $pv) = $row;
			$female = 'female' == $sex;
			$hue = $female ? 13 : 197;
			$age = explode('-', $age);
			$saturation = 100 - ($age[0] * 2);
			$color = sprintf('hsl(%d, %d%%, 50%%)', $hue, $saturation);
			$highlight = sprintf('hsl(%d, %d%%, 70%%)', $hue, $saturation);
			$data[] = [
				'value' => intval($pv),
		        'color' => $color,
		        'highlight' => $highlight,
		        'label' => sprintf('%s（%d〜%d歳）', ( $female ? '女性' : '男性' ), $age[0], $age[1]),
			];
		}
		return $data;
	}

}