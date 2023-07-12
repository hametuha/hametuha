<?php
namespace Hametuha\WpApi;

use Hametuha\WpApi\Pattern\AnalyticsPattern;

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
	 * Get arguments for method.
	 *
	 * @param string $method 'GET', 'POST', 'PUSH', 'PATCH', 'DELETE', 'HEAD', 'OPTION'
	 *
	 * @return array
	 */
	protected function get_arguments( $method ) {
		$args = [
			'user_id' => [
				'required'          => true,
				'validate_callback' => [ $this, 'validate_user_id' ],
			],
		];
		switch ( $method ) {
			case 'GET':
				$args = $this->add_date_fields( $args, 30 );
				return $args;
			default:
				return [];
		}
	}

	/**
	 * Get request.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function handle_get( \WP_REST_Request $request ) {
		// Create date dimension
		$params = [
			'start'     => $request->get_param( 'from' ),
			'end'       => $request->get_param( 'to' ),
			'post_type' => 'post,series,news',
			'limit'     => 1000,
		];
		$user_id = $request->get_param( 'user_id' );
		switch ( $user_id ) {
			case 'me':
				$params['author'] = (string) get_current_user_id();
				break;
			case 'all':
				// Do nothing.
				break;
			default:
				$params['author'] = (string) $request->get_param( 'user_id' );
				break;
		}
		return new \WP_REST_Response( [
			'start'          => $params['start'],
			'end'            => $params['end'],
			'records'        => array_map( function( $row ) {
				list( $date, $post_type ) = $row;
				$pv = $row[ count( $row ) - 1 ];
				$post_type_obj = get_post_type_object( $post_type );
				return [
					'date'      => preg_replace( '#(\d{4})(\d{2})(\d{2})#u', '$1-$2-$3', $date ),
					'post_type' => $post_type_obj ? $post_type_obj->label : $post_type,
					'pv'        => (int) $pv,
				];
			}, $this->ga4->chronic_popularity( $params ) ),
			'rankings'       => array_map( function( $rank ) {
				list( $path ) = $rank;
				$pv = $rank[ count( $rank ) - 1 ];
				if ( $post_id = url_to_postid( home_url( $path ) ) ) {
					$label = get_post_type_object( get_post_type( $post_id ) )->label;
					$url   = get_permalink( $post_id );
				} else {
					$label = '不明';
					$url   = '#';
				}
				return [
					'title' => $post_id ? get_the_title( $post_id ) : '削除された投稿',
					'type'  => $label,
					'url'   => $url,
					'pv'    => (int) $pv,
				];
			}, $this->ga4->popular_posts( array_merge( $params, [
				'limit' => 100,
			] ) ) ),
		] );
	}

}
