<?php
namespace Hametuha\WpApi;

/**
 * Show user PV
 *
 * @package hametuha
 */
class UserPv extends AnalyticsPattern {
	/**
	 * Should return route
	 *
	 * @return string
	 */
	protected function get_route() {
		return 'stats/access/(?P<user_id>\d+|me|all)';
	}

	/**
	 * Check availability
	 *
	 * Override this function if some condition exists like
	 * plugin dependencies.
	 *
	 * @return bool
	 */
	protected function is_available() {
		return class_exists( 'Gianism\\Bootstrap' );
	}


	/**
	 * Get arguments for method.
	 *
	 * @param string $method 'GET', 'POST', 'PUSH', 'PATCH', 'DELETE', 'HEAD', 'OPTION'
	 *
	 * @return array
	 */
	protected function get_arguments( $method ) {
		$args = [
			'user_id' => [
				'required' => true,
				'validate_callback' => function( $var ) {
					return ( is_numeric( $var ) || in_array( $var, [ 'me', 'all'] ) ) ?: new \WP_Error( 'malformat', 'ユーザーIDの指定が不正です。' );
				}
			],
		];
		switch ( $method ) {
			case 'GET':
				$args = $this->add_date_fields( $args, 30 );
				return $args;
				break;
			default:
				return [];
				break;
		}
	}

	/**
	 * Get request.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function handle_get( \WP_REST_Request $request ) {
		$user_id = $request->get_param( 'user_id' );
		switch ( $user_id ) {
			case 'me':
				$user_id = get_current_user_id();
				break;
			case 'all':
				$user_id = 0;
				$filters = '';
				break;
		}
		// Create date dimension
		$start = $request->get_param( 'from' );
		$end   = $request->get_param( 'to' );
		$date_dimension = $this->proper_metrics( $start, $end );
		// Set default dimension
		$filters = sprintf( 'ga:dimension2==%d;ga:dimension1=~post|series|news', $user_id );
		$pv_params = [
			'dimensions' => implode( ',', [ $date_dimension, 'ga:dimension1' ] ),
			'sort'       => $date_dimension,
		];
		$rank_params = [
			'dimensions' => implode( ',', [ 'ga:pagePath' ] ),
			'sort' => '-ga:pageviews',
			'max-results' => 20,
		];
		if ( $user_id ) {
			$pv_params['filters'] = $filters;
			$rank_params['filters'] = $filters;
		}
		return new \WP_REST_Response( [
			'start' => $start,
			'end'   => $end,
			'date_dimension' => $date_dimension,
			'records' => array_map( function( $row ) {
				list( $date, $post_type, $pv ) = $row;
				return [
					'date' => preg_replace( '#(\d{4})(\d{2})(\d{2})#u', '$1-$2-$3', $date ),
					'post_type' => get_post_type_object( $post_type )->label,
					'pv'   => (int) $pv,
				];
			}, $this->fetch( $start, $end, 'ga:pageviews', $pv_params, true ) ),
			'rankings' => array_map( function( $rank ) {
				list( $path, $pv ) = $rank;
				if ( $post_id = url_to_postid( home_url( $path ) ) ) {
					$label = get_post_type_object( get_post_type( $post_id ) )->label;
					$url = get_permalink( $post_id );
				} else {
					$label = '不明';
					$url = '#';
				}
				return [
					'title' => $post_id ? get_the_title( $post_id ) : '削除された投稿',
					'type' => $label,
					'url' => $url,
					'pv' => (int) $pv,
				];
			}, $this->fetch( $start, $end, 'ga:pageviews', $rank_params, true ) ),
		] );
	}

}
