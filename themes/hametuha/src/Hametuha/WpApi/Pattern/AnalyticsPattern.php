<?php

namespace Hametuha\WpApi\Pattern;


use Hametuha\Service\GoogleAnalyticsDataAccessor;
use WPametu\API\Rest\WpApi;

/**
 * Analytics api
 *
 * @package hametuha
 * @package Gianism
 * @property \Gianism\Plugins\Analytics $google
 * @property \Google_Service_Analytics $ga
 * @property-read GoogleAnalyticsDataAccessor $ga4
 * @property array  $profile
 * @property \wpdb  $db
 * @property string $view_id
 * @property string $table
 */
abstract class AnalyticsPattern extends WpApi {

	/**
	 * Check availability
	 *
	 * Override this function if some condition exists like
	 * plugin dependencies.
	 *
	 * @return bool
	 */
	protected function is_available() {
		return class_exists( 'Kunoichi\GaCommunicator' );
	}

	/**
	 * Return today string in Y-m-d
	 *
	 * @return string
	 */
	protected function today() {
		$date = new \DateTime( 'now', wp_timezone() );
		return $date->format( 'Y-m-d' );
	}

	/**
	 * Check string.
	 *
	 * @param int $n Days to decline.
	 * @return string
	 */
	protected function n_days_ago( $n ) {
		$now = new \DateTime( 'now', wp_timezone() );
		$now->sub( new \DateInterval( 'P' . $n . 'D' ) );
		return $now->format( 'Y-m-d' );
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
		$fields[ $key ] = [
			'required'          => true,
			'default'           => $default,
			'validate_callback' => function ( $var ) use ( $key ) {
				return $this->str->is_date( $var ) ?: new \WP_Error( 'malformat', sprintf( '%sは日付形式でなければなりません。', $key ) );
			},
		];
	}

	/**
	 * Fetch data from Google Analytics API
	 *
	 * @deprecated
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
	 * Typical REST arguments.
	 *
	 * @param $method
	 * @return array
	 */
	protected function get_arguments( $method ) {
		return $this->add_date_fields( $this->user_id_arg(), 30 );
	}

	/**
	 * Is user id is valid.
	 *
	 * @param mixed $var Variable.
	 * @return bool|\WP_Error
	 */
	public function validate_user_id( $var ) {
		return ( is_numeric( $var ) || in_array( $var, [ 'me', 'all' ] ) ) ?: new \WP_Error( 'malformat', __( 'ユーザーIDの指定が不正です。', 'hametuha' ) );
	}

	/**
	 * Parse permission
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return bool
	 */
	public function permission_callback( $request ) {
		if ( 'me' === $request->get_param( 'user_id' ) ) {
			return current_user_can( 'read' );
		} else {
			return current_user_can( 'edit_others_posts' );
		}
	}


	/**
	 * Return metrics considering date range
	 *
	 * @deprecated
	 * @param string $start
	 * @param string $end
	 * @return string
	 */
	protected function proper_metrics( $start, $end ) {
		$start = new \DateTime( "{$start} 00:00:00" );
		$end   = new \DateTime( "{$end} 00:00:00" );
		$diff  = $start->diff( $end )->days;
		if ( $diff > 365 * 1 ) {
			// Over 2 years, year month.
			return 'ga:yearMonth';
		} else {
			return 'ga:date';
		}
	}

	/**
	 * Convert request to User ID.
	 *
	 * @param int|string $id_or_string int, "me", or "all"
	 * @return int User ID.
	 */
	protected function to_author_id( $id_or_string ) {
		switch ( $id_or_string ) {
			case 'me':
				return get_current_user_id();
			case 'all':
				return 0;
			default:
				return (int) $id_or_string;
		}
	}

	/**
	 * User ID arguments.
	 *
	 * @return array
	 */
	protected function user_id_arg() {
		return [
			'user_id' => [
				'required'          => true,
				'validate_callback' => [ $this, 'validate_user_id' ],
			],
		];
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
				return \Gianism\Plugins\Analytics::get_instance();
				break;
			case 'ga4':
				return GoogleAnalyticsDataAccessor::get_instance();
			default:
				return parent::__get( $name );
				break;
		}
	}
}
