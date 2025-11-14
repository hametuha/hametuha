<?php

/**
 * Googleのランキングを取得する
 *
 * @param string $start
 * @param string $end
 * @param array $params
 * @param string $metrics
 *
 * @return WP_Error|array
 */
function hametuha_ga_ranking( $start, $end, $params = [], $metrics = 'screenPageViews' ) {
	if ( ! class_exists( 'Kunoichi\GaCommunicator' ) ) {
		return new WP_Error( 'api_error', __( '必要なライブラリga-commmunicatorがなく、APIを利用できません。', 'hametuha' ) );
	}
	$params                = wp_parse_args( $params, [
		'max-results'     => 10,
		'dimensions'      => 'pageTitle',
		'sort'            => 'screenPageViews',
		'order'           => 'DESC',
		'dimensionFilter' => [],
	] );
	$request['dateRanges'] = [
		[
			'startDate' => $start,
			'endDate'   => $end,
		],
	];
	$request['limit']      = $params['max-results'];
	$request['orderBys']   = [
		[
			'metric' => [
				'metricName' => $params['sort'],
			],
			'desc'   => ( 'DESC' === $params['order'] ),
		],
	];
	if ( $params['dimensionFilter'] ) {
		$request['dimensionFilter'] = $params['dimensionFilter'];
	}
	foreach ( [
		'dimensions' => $params['dimensions'],
		'metrics'    => $metrics,
	] as $key => $values ) {
		$request[ $key ] = [];
		foreach ( explode( ',', $values ) as $value ) {
			$request[ $key ][] = [
				'name' => trim( $value ),
			];
		}
	}
	$result = \Kunoichi\GaCommunicator::get_instance()->ga4_get_report( $request, function ( $row ) {
		$return = [];
		foreach ( [ 'dimensionValues', 'metricValues' ] as $key ) {
			foreach ( $row[ $key ] as $v ) {
				$return[] = $v['value'];
			}
		}
		return $return;
	} );
	return $result;
}

/**
 * 人気のランキングを取得する
 *
 * @param string $start
 * @param string $end
 * @param string $post_type
 * @param int    $limit
 *
 * @return WP_Error|array
 */
function hametuha_hot_posts( $start, $end, $post_type = 'post', $limit = 3 ) {
	$params = [
		'dimensionFilter' => [
			'filter' => [
				'fieldName'    => 'customEvent:post_type',
				'stringFilter' => [
					'matchType' => 'EXACT',
					'value'     => $post_type,
				],
			],
		],
		'dimensions'      => 'pageTitle,pagePath,customEvent:post_type',
		'max-results'     => $limit,
	];
	return hametuha_ga_ranking( $start, $end, $params );
}
