<?php

namespace Hametuha\WpApi;

/**
 * Show user audiences
 *
 * @package hametuha
 */
class AuthorTraffic extends Pattern\AnalyticsPattern {

	/**
	 * @inheritDoc
	 */
	protected function get_route() {
		return 'stats/traffic/(?P<user_id>\d+|me|all)';
	}

	/**
	 * Returns audiences' info.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function handle_get( $request ) {
		// Create date dimension
		$params = [
			'start'     => $request->get_param( 'from' ),
			'end'       => $request->get_param( 'to' ),
		];
		$user_id = $this->to_author_id( $request->get_param( 'user_id' ) );
		// Fill profile pv.
		$profiles = [];
		$result   = $this->ga4->audiences( 'profile', $params['start'], $params['end'], $user_id );
		if ( 'yearMonth' === $this->ga4->proper_range_dimension( $params['start'], $params['end'] ) ) {
			list( $from_year, $from_month ) = explode( '-', $params['start'] );
			list( $to_year, $to_month )     = explode( '-', $params['end'] );
			foreach ( range( $from_year, $to_year ) as $y ) {
				for ( $i = 1; $i <= 12; $i++ ) {
					if ( $from_month < $i ) {
						// skip
						continue 1;
					}
					if ( sprintf(  '%s%02d', $y, $i ) > sprintf( '%s%02d', $to_year, $to_month ) ) {
						break 2;
					}
					$profiles[ sprintf( '%d/%02d', $y, $i ) ] = 0;
				}
			}
			foreach ( $result as list( $date, $pv ) ) {
				$date = preg_replace( '/(\d4)(\d2)/', '$1/$2', $date );
				if ( isset( $profiles[ $date ] ) ) {
					$profiles[ $date ] = (int) $pv;
				}
			}
		} else {
			$date = new \DateTime( $params['start'], wp_timezone() );
			while ( $date->format( 'Y-m-d' ) <= $params['end'] ) {
				$profiles[ $date->format( 'Y/m/d' ) ] = 0;
				$date->add( new \DateInterval( 'P1D' ) );
			}
			foreach ( $result as list( $date, $pv ) ) {
				$date = preg_replace( '/(\d{4})(\d{2})(\d{2})/', '$1/$2/$3', $date );
				if ( isset( $profiles[ $date ] ) ) {
					$profiles[ $date ] = (int) $pv;
				}
			}
		}
		$profiles_result = [];
		foreach ( $profiles as $date => $pv ) {
			$profiles_result[] = [ $date, $pv ];
		}
		$response = [
			'start' => $params['start'],
			'end'   => $params['end'],
			'source' => array_map( function( $row ) {
				return $row;
			}, $this->ga4->audiences( 'source', $params['start'], $params['end'], $user_id ) ),
			'contributors' => array_map( function( $row ) use ( $user_id ) {
				$contributor = (int) $row[0];
				if ( ! $contributor ) {
					$author = __( 'ゲスト', 'hametuha' );
				} elseif ( 1 === $contributor ) {
					$author = __( '破滅派自動', 'hametuha' );
				} elseif ( $user_id === $contributor ) {
					$author = __( 'あなた', 'hametuha' );
				} elseif ( get_userdata( $contributor ) ) {
					$author = get_userdata( $contributor )->display_name;
				} else {
					$author = __( '退会したユーザー', 'hametuha' );
				}
				return [ $author, $contributor, $row[ count( $row ) - 1 ] ];
			}, $this->ga4->audiences( 'referrer', $params['start'], $params['end'], $user_id ) ),
			'profiles' => $profiles_result,
		];

		return new \WP_REST_Response( $response, 200 );
	}
}
