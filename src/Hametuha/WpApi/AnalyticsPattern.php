<?php

namespace Hametuha\WpApi;


use Gianism\Plugins\Analytics;
use WPametu\API\Rest\WpApi;

/**
 * Analytics api
 *
 * @package hametuha
 * @package Gianism
 * @property \Gianism\Plugins\Analytics $google
 * @property \Google_Service_Analytics $ga
 * @property array  $profile
 * @property \wpdb  $db
 * @property string $view_id
 * @property string $table
 */
abstract class AnalyticsPattern extends WpApi {

	/**
	 * Return today string in Y-m-d
	 *
	 * @return string
	 */
	protected function today() {
		return date_i18n( 'Y-m-d', current_time( 'timestamp' ) );
	}

	/**
	 * Check string.
	 *
	 * @param int $n Days to decline.
	 * @return string
	 */
	protected function n_days_ago( $n ) {
		$now = current_time( 'timestamp' );
		$now -= 60 * 60 * 24 * $n;
		return date_i18n( 'Y-m-d', $now );
	}

	/**
	 * Fill default field
	 *
	 * @param array $field
	 * @param int   $n_days_ago
	 * @return array
	 */
	protected function add_date_fields( $field, $n_days_ago = 7 ) {
		$this->add_date( $field, 'from', $this->n_days_ago( $n_days_ago ) );
		$this->add_date( $field, 'to', $this->today() );
		return $field;
	}

	/**
	 * Add default fields.
	 *
	 * @param array $fields
	 * @param string $key
	 * @param string $default
	 */
	protected function add_date( &$fields, $key, $default ) {
		$fields[$key] = [
			'required' => true,
			'default'  => $default,
			'validation_callback' => [ $this->str, 'is_date' ],
		];
	}

	/**
	 * Fetch data from Google Analytics API
	 *
	 * @param string $start_date Date string
	 * @param string $end_date Date string
	 * @param string $metrics CSV of metrics E.g., 'ga:visits,ga:pageviews'
	 * @param array  $params Option params below
	 * @param bool   $throw If set to true, throws exception
	 *
	 * @opt_param string dimensions A comma-separated list of Analytics dimensions. E.g., 'ga:browser,ga:city'.
	 * @opt_param string filters A comma-separated list of dimension or metric filters to be applied to Analytics data.
	 * @opt_param int max-results The maximum number of entries to include in this feed.
	 * @opt_param string segment An Analytics advanced segment to be applied to data.
	 * @opt_param string sort A comma-separated list of dimensions or metrics that determine the sort order for Analytics data.
	 * @opt_param int start-index An index of the first entity to retrieve. Use this parameter as a pagination mechanism along with the max-results parameter.
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function fetch( $start_date, $end_date, $metrics, $params = [], $throw = false ) {
		try {
			if ( ! $this->ga || ! $this->view_id ) {
				throw new \Exception( 'Google Analytics is not connected.', 500 );
			}
			$result = $this->ga->data_ga->get( 'ga:' . $this->view_id, $start_date, $end_date, $metrics, $params );
			if ( $result && count( $result->rows ) > 0 ) {
				return $result->rows;
			} else {
				return [];
			}
		} catch ( \Exception $e ) {
			if ( $throw ) {
				throw $e;
			} else {
				error_log( sprintf( '[Gianism GA Error %s] %s', $e->getCode(), $e->getMessage() ) );
			}

			return [];
		}
	}

	/**
	 * Getter
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'ga':
				try {
					return $this->google->ga;
				} catch ( \Exception $e ) {
					return null;
				}
				break;
			case 'profile':
				return $this->google->ga_profile;
				break;
			case 'view_id':
				return $this->profile['view'];
				break;
			case 'google':
				return Analytics::get_instance();
				break;
			default:
				return parent::__get( $name );
				break;
		}
	}
}
