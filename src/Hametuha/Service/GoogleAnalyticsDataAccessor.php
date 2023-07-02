<?php

namespace Hametuha\Service;


use WPametu\Pattern\Singleton;

/**
 * Access Google Analytics Data API.
 *
 * @package hametuha
 * @property-read \Kunoichi\GaCommunicator $ga    An instance of GaCommunicator
 * @property-read string                   $table Table name.
 * @property-read \wpdb                    $db    Database object.
 */
class GoogleAnalyticsDataAccessor extends Singleton {

	/**
	 * @param array $args
	 * @return array
	 * @throws \Exception
	 */
	protected function parse_args( $args ) {
		try {
			$date = new \DateTime( 'now', new \DateTimeZone( wp_timezone_string() ) );
			$end = $date->format( 'Y-m-d' );
			$date->sub( new \DateInterval( 'P7D' ) );
			$start = $date->format( 'Y-m-d' );
		} catch ( \Exception $e ) {
			$start = $end = date_i18n( 'Y-m-d' );
		}
		return wp_parse_args( $args, [
			'start'     => $start,
			'end'       => $end,
			'post_type' => '',
			'author'    => '',
			'limit'     => 100,
			'category'  => '',
			'at_least'  => 1,
		] );
	}

	/**
	 * Save popular posts to database.
	 *
	 * @param array $params Parameters.
	 * @return int|\WP_Error
	 */
	public function save_popular_posts( $category, $params = [] ) {
		$params    = $this->parse_args( $params );
		$calc_date = $params['end'];
		$result = $this->popular_posts( $params );
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		// Save all results.
		$updated = 0;
		foreach ( $result as list( $path, $post_type, $author, $term, $pv ) ) {
			$post_id = url_to_postid( $path );
			if ( ! $post_id ) {
				continue 1;
			}
			if ( $this->save_record( $category, $post_id, (int) $pv, $calc_date ) ) {
				$updated++;
			}
		}
		return $updated;
	}

	/**
	 * Get popular posts.
	 *
	 * @param array $params Parameters.
	 * @return array[]|\WP_Error
	 */
	public function popular_posts( $params = [] ) {
		$params = $this->parse_args( $params );
		$request = [
			'dimensions'      => [
				[
					'name' => 'pagePath',
				],
				[
					'name' => 'customEvent:post_type',
				],
				[
					'name' => 'customEvent:author',
				],
				[
					'name' => 'customEvent:category',
				],
			],
			'dateRanges'      => [
				[
					'startDate' => $params['start'],
					'endDate'   => $params['end'],
				],
			],
			'limit'           => $params['limit']
		];
		$filters = [];
		if ( $params['post_type'] ) {
			$filters[] = [
				'fieldName'    => 'customEvent:post_type',
				'stringFilter' => [
					'matchType' => 'EXACT',
					'value'     => $params['post_type'],
				],
			];
		}
		if ( $params['category'] ) {
			$filters[] = [
				'fieldName'    => 'customEvent:category',
				'stringFilter' => [
					'matchType' => 'CONTAINS',
					'value'     => (string) $params['category'],
				],
			];
		}
		if ( $params['author'] ) {
			$filters[] = [
				'fieldName'    => 'customEvent:author',
				'stringFilter' => [
					'matchType' => 'EXACT',
					'value'     => $params['author'],
				],
			];
		}
		if ( 1 === count( $filters ) ) {
			// 1 Filter.
			$request['dimensionFilter'] = [
				'filter' => $filters[0],
			];
		} elseif ( ! empty( $filters ) ) {
			// Multiple Filters.
			$request['dimensionFilter'] = [
				'andGroup' => [
					'expressions' => array_map( function( $filter ) {
						return [
							'filter' => $filter,
						];
					}, $filters ),
				],
			];
		}
		if ( $params['at_least'] ) {
			$request['metricFilter'] = [
				'filter' => [
					'fieldName'    => 'screenPageViews',
					'numericFilter' => [
						'operation' => 'GREATER_THAN_OR_EQUAL',
						'value'     => [
							'int64Value' => (string) $params['at_least'],
						],
					],
				],
			];
		}
		if ( 0 < $params['offset'] ) {
			$request['offset'] = $params['offset'];
		}
		return $this->fetch( $request );
	}

	/**
	 * Get report.
	 *
	 * @see \Kunoichi\GaCommunicator
	 * @param array $request
	 * @return array[] Report results consists of dimensions and metrics.
	 */
	public function fetch( array $request = [] ) {
		if ( is_null( $this->ga ) ) {
			return [];
		}
		$result = $this->ga->ga4_get_report( $request, function( $row ) {
			// Flatten result row.
			$filtered = [];
			foreach ( [ 'dimensionValues', 'metricValues' ] as $key ) {
				if ( ! isset( $row[ $key ] ) ) {
					continue;
				}
				foreach  ( $row[ $key ] as $value ) {
					$filtered[] = $value['value'];
				}
			}
			return $filtered;
		} );
		if ( is_wp_error( $result ) ) {
			// This is error. Save log.
			error_log( $result->get_error_message() );
			return [];
		}
		return $result;
	}

	/**
	 * @param string $category
	 * @param int    $id
	 * @param int    $value
	 * @param string $date     Datetime.
	 * @return bool|int|\mysqli_result|resource|null
	 */
	public function save_record( $category, $id, $value, $date) {
		return $this->db->insert(
			$this->table,
			[
				'category'     => $category,
				'object_id'    => $id,
				'object_value' => $value,
				'calc_date'    => $date,
			],
			[ '%s', '%d', '%d', '%s' ]
		);
	}

	/**
	 * Getter.
	 *
	 * @param string $name Property name.
	 * @return mixed
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'db':
				global $wpdb;
				return $wpdb;
			case 'table':
				return $this->db->prefix . 'wpg_ga_ranking';
			case 'ga':
				if ( class_exists( 'Kunoichi\GaCommunicator' ) ) {
					return \Kunoichi\GaCommunicator::get_instance();
				} else {
					return null;
				}
			default:
				return null;
		}
	}
}
