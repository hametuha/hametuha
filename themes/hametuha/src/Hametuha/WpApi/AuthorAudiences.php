<?php

namespace Hametuha\WpApi;

/**
 * Show user audiences
 *
 * @package hametuha
 */
class AuthorAudiences extends Pattern\AnalyticsPattern {

	/**
	 * @inheritDoc
	 */
	protected function get_route() {
		return 'stats/audiences/(?P<user_id>\d+|me|all)';
	}

	/**
	 * Returns audiences' info.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function handle_get( $request ) {
		// Get user ID.
		switch ( $request['user_id'] ) {
			case 'me':
				$user_id = get_current_user_id();
				break;
			case 'all':
				$user_id = 0;
				break;
			default:
				$user_id = (int) $request['user_id'];
				break;
		}
		$start    = $request['from'];
		$end      = $request['to'];
		$response = [];
		foreach ( [
			'gender',
			'new',
			'generation',
			'region',
		] as $key ) {
			$response[ $key ] = $this->ga4->audiences( $key, $start, $end, $user_id );
		}
		return new \WP_REST_Response( $response, 200 );
	}
}
