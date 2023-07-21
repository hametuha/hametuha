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
	 * Typical arguemnts.
	 *
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
			'offset'    => 0,
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
	 * Typical post dimension.
	 *
	 * @return array[]
	 */
	protected function posts_dimensions() {
		return [
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
		];
	}

	/**
	 * Get proper time range.
	 *
	 * @param string $start Datetime format.
	 * @param string $end   Datetime format.
	 * @return string
	 */
	public function proper_range_dimension( $start, $end ) {
		$start_date = new \DateTime( "{$start} 00:00:00" );
		$end_date   = new \DateTime( "{$end} 00:00:00" );
		$diff       = $start_date->diff( $end_date )->days;
		return ( $diff > 365 ) ? 'yearMonth' : 'date';
	}

	/**
	 * Get chronical popularity.
	 *
	 * @param $params
	 * @return array
	 */
	public function chronic_popularity( $params ) {
		$dimensions = [];
		$params     = $this->parse_args( $params );
		// Default dimension.
		$dimensions[] = [
			'name' => $this->proper_range_dimension( $params['start'], $params['end'] ),
		];
		// Set filters.
		$filters    = [];
		if ( $params['post_type'] ) {
			$dimensions[] = [
				'name' => 'customEvent:post_type',
			];
			$filters[] = $this->post_type_filter( $params['post_type'] );
		}
		if ( $params['author'] ) {
			$dimensions[] = [
				'name' => 'customEvent:author',
			];
			if ( is_numeric( $params['author'] ) ) {
				$filters[]    = [
					'fieldName'    => 'customEvent:author',
					'stringFilter' => [
						'matchType' => 'EXACT',
						'value'     => (string) $params['author'],
					],
				];
			}
		}
		$request = [
			'dimensions'      => $dimensions,
			'dateRanges'      => [
				[
					'startDate' => $params['start'],
					'endDate'   => $params['end'],
				],
			],
			'orderBys'        => [
				[
					'dimension' => [
						'dimensionName' => $dimensions[0]['name'],
						'orderType'     => 'NUMERIC',
					],
					'desc'      => false,
				],
			],
			'limit'           => $params['limit']
		];
		if ( ! empty( $filters ) ) {
			$request[ 'dimensionFilter' ] = $this->convert_filters( $filters );
		}
		if ( 0 < $params['offset'] ) {
			$request['offset'] = $params['offset'];
		}
		return $this->fetch( $request );
	}

	/**
	 * Get popular posts.
	 *
	 * @param array $params                Parameters.
	 * @return array[]|\WP_Error
	 */
	public function popular_posts( $params = [] ) {
		$params     = $this->parse_args( $params );
		$dimensions = $this->posts_dimensions();
		$request = [
			'dimensions'      => $dimensions,
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
			$filters[] = $this->post_type_filter( $params['post_type'] );
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
		$request['dimensionFilter'] = $this->convert_filters( $filters );
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
	 * Return filter if post type is set.
	 *
	 * @param string|string[] $post_types CSV value of post type.
	 * @return array
	 */
	protected function post_type_filter( $post_types ) {
		if ( ! is_array( $post_types ) ) {
			$post_types = array_map( 'trim', explode( ',', $post_types ) );
		}
		if ( 1 === count( $post_types ) ) {
			return [
				'fieldName' => 'customEvent:post_type',
				'stringFilter' => [
					'matchType' => 'EXACT',
					'value' => $post_types[0],
				],
			];
		} else {
			return [
				'fieldName' => 'customEvent:post_type',
				'inListFilter' => [
					'values' => $post_types,
				],
			];
		}
	}

	/**
	 * Convert filter to filter expression.
	 *
	 * @param array $filters
	 * @param bool  $or If true, groups are or group.
	 * @return array|array[]
	 */
	protected function convert_filters( $filters, $or = false ) {
		if ( 1 < count( $filters ) ) {
			$key = $or ? 'orGroup' : 'andGroup';
			return [
				$key => [
					'expressions' => array_map( [ $this, 'filter_or_not' ], $filters ),
				],
			];
		} else {
			return $this->filter_or_not( $filters[0] );
		}
	}

	/**
	 * Convert filter to not or expression.
	 *
	 * @param array $filter
	 * @return array
	 */
	protected function filter_or_not( $filter ) {
		if ( ! empty( $filter['not'] ) ) {
			unset( $filter['not'] );
			return [
				'notExpression' => [
					'filter' => $filter,
				],
			];
		} else {
			return [
				'filter' => $filter,
			];
		}
	}

	/**
	 * Get audience data.
	 *
	 * @param string $group     Group of Audience. 'gender', 'generation', 'new', and 'region' are available.
	 * @param string $start     Start date YYYY-MM-DD
	 * @param string $end       End date YYYY-MM-DD
	 * @param int    $author_id Author ID. If 0, all authors.
	 * @return array[]|\WP_Error
	 */
	public function audiences( $group, $start, $end, $author_id = 0 ) {
		$author = null;
		if ( $author_id ) {
			$author = get_userdata( $author_id );
			if ( ! $author ) {
				return new \WP_Error( 'user_not_found', __( '指定された作者は存在しません。', 'hametuha' ) );
			}
		}
		$request = [
			'dateRanges' => [
				[
					'startDate' => $start,
					'endDate'   => $end,
				],
			],
			'metrics' => [
				[
					'name' => 'sessions',
				],
			],
			'orderBys' => [
				[
					'dimension' => [
						'dimensionName' => 'sessions',
						'orderType'     => 'NUMERIC',
					],
					'desc'      => true,
				],
			],
			'limit'      => 100,
		];
		$groups = [
			'gender'     => [
				'dimensions' => [
					[
						'name' => 'userGender',
					],
				],
			],
			'generation' => [
				'dimensions' => [
					[
						'name' => 'userAgeBracket',
					],
				],
			],
			'new'        => [
				'dimensions' => [
					[
						'name' => 'newVsReturning',
					],
				],
			],
			'region'       => [
				'dimensions' => [
					[
						'name' => 'region',
					]
				],
				"limit" => 50,
			],
			'source' => [
				'dimensions' => [
					[
						'name' => 'firstUserSource',
					],
				],
				'limit' => 20,
			],
			'referrer' => [
				'dimensions' => [
					[
						'name' => 'firstUserMedium',
					],
				],
				'filters' => [
					[
						'fieldName' => 'firstUserCampaignName',
						'inListFilter' => [
							'values' => [ 'share-single', 'share-auto', 'share-dashboard' ],
						],
					],
				],
				'limit' => 20,
			],
			'profile' => [
				'dimensions' => [
					[
						'name' => $this->proper_range_dimension( $start, $end ),
					],
				],
				'filters' => [
					[
						'fieldName' => 'pagePath',
						'stringFilter' => [
							'matchType' => 'BEGINS_WITH',
							'value' => '/doujin/detail/',
						],
					],
				],
				'limit' => 20,
			]
		];
		if ( ! array_key_exists( $group, $groups ) ) {
			return new \WP_Error( 'audience_not_found', __( '指定された読者グループは存在しません。', 'hametuha' ) );
		}
		$group_option = $groups[ $group ];
		$filters      = [];
		if ( ! empty( $group_option['filters'] )) {
			$filters = $group_option['filters'];
			unset( $group_option['filters'] );
		}
		$request = array_merge( $request, $group_option );
		if ( $author ) {
			switch ( $group ) {
				case 'profile':
					$filters[] = [
						'fieldName' => 'pagePath',
						'stringFilter' => [
							'matchType' => 'BEGINS_WITH',
							'value' => '/doujin/detail/' . $author->user_nicename,
						],
					];
					break;
				default:
					$request['dimensions'][] = [
						'name' => 'customEvent:author',
					];
					$filters[] = [
						'fieldName'    => 'customEvent:author',
						'stringFilter' => [
							'matchType' => 'EXACT',
							'value'     => (string) $author->ID,
						],
					];
					break;
			}
		}
		// Setup filter.
		if ( ! empty( $filters ) ) {
			$request['dimensionFilter'] = $this->convert_filters( $filters );
		}
		return $this->fetch( $request );
	}

	/**
	 * Save record to database.
	 *
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
