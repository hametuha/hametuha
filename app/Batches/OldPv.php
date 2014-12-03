<?php

namespace Hametuha\Batches;


use WPametu\Tool\Batch;
use WPametu\Tool\BatchResult;

class OldPv extends Batch
{

	/**
	 * @var int
	 */
	protected $per_process = 200;

	/**
	 * Return title
	 *
	 * @return string
	 */
	protected function get_title() {
		return 'Old PV';
	}

	/**
	 * Return description
	 *
	 * @return string
	 */
	protected function get_description() {
		return '古いPVをGoogle Analyticsから取得する。';
	}

	/**
	 * Do process
	 *
	 * @param $page
	 *
	 * @return int Next page. 0 means no more process.
	 * @throws \Exception
	 */
	public function process( $page ) {
		/** @var \Gianism\Service\Google $google */
		if( !class_exists('Gianism\Service\Google') || !($google = \Gianism\Service\Google::get_instance()) || !$google->ga ){
			throw new \Exception('Gianismが有効ではありません');
		}
		$offset = (max($page, 1) - 1) * $this->per_process;
		$start_date = '2008-05-01'; // これぐらいの日付からWordPressになったっぽい。
		$end_date = '2014-11-01';
		$result = $result = $google->ga->data_ga->get('ga:'.$google->ga_profile['view'], $start_date, $end_date, 'ga:pageviews', [
			'dimensions' => 'ga:pagePath',
			'sort' => '-ga:pageviews',
			'max-results' => $this->per_process,
			'start-index' => $offset + 1,
		]);

		if( ($count = count($result->rows)) ){
			foreach( $result->rows as $row ){
				list($path, $pv) = $row;
				$post_id = 0;
				if( preg_match('/^\/(syoko\/)?(novel|series|etude|poeme|essaie|dialogue|repo|drama)\/([0-9]+)\/?/u', $path, $match) ){
					$post_id = $match[3];
				}elseif( preg_match('/^\/(syoko\/)?(novel|series|etude|poeme|essaie|dialogue|repo|drama)\/([^\/]+)\/([0-9]+)\/?/u', $path, $match)  ){
					$post_id = $match[4];
				}
				if( $post_id && get_post($post_id) ){
					$old_pv = (int) get_post_meta($post_id, '_old_pv', true);
					$old_pv += intval($pv);
					update_post_meta($post_id, '_old_pv', $old_pv);
				}
			}
			$next = $count == $this->per_process;
			$count += $offset;
			$total = $result->totalResults;
		}else{
			$count = 0;
			$total = 0;
			$next = false;
		}
		return new BatchResult($count, $total, $next);
	}


}
