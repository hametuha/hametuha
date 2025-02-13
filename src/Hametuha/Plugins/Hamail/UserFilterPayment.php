<?php
namespace Hametuha\Plugins\Hamail;

use Hametuha\Hamail\Pattern\Filters\UserFilterInputPattern;

/**
 * 支払いを受けたことのあるユーザー
 */
class UserFilterPayment extends UserFilterInputPattern {

	public function id(): string {
		return 'payment-received';
	}

	public function description(): string {
		return __( '支払いを受けた期間', 'hametuha' );
	}

	protected function placeholder() {
		return 'YYYY-MM or YYYY-MM-DD,YYYY-MM-DD';
	}

	protected function help_text() {
		return __( 'カンマで区切った場合は指定期間、年月だけの場合はその月が範囲になります。', 'hametuha' );
	}

	public function validate_callback( $values, \WP_REST_Request $request ) {
		foreach ( $values as $value ) {
			if ( empty( $value ) ) {
				continue;
			}
			$days = $this->convert_users( $value );
			if ( is_wp_error( $days ) ) {
				return $days;
			}
		}
		return true;
	}

	protected function convert_users( $args, $values = [], $original_args = [] ) {
		foreach ( $values as $value ) {
			if ( empty( $value ) ) {
				continue;
			}
			$days = $this->convert_from_and_to( $value );
			if ( is_wp_error( $days ) ) {
				return $args;
			}
			list( $from, $to ) = $days;
			$args['paid_since'] = $from;
			$args['paid_till']  = $to;
		}
		return $args;
	}

	/**
	 * 日付を受け取り、日付範囲に直す
	 *
	 * @param string $date_string 日付形式（Y-mやY-m-d,Y-m-dなど）
	 * @return string[]|\WP_Error
	 */
	protected function convert_from_and_to( $date_string ) {
		try {
			$days = array_map( 'trim', explode( ',', $date_string ) );
			if ( 1 === count( $days ) ) {
				// Single day.
				if ( preg_match( '/^\d{4}-\d{2}$/', $days[0] ) ) {
					$from      = new \DateTime( $this->to_ymd( $days[0], '-01' ), wp_timezone() );
					$from_date = $from->format( 'Y-m-01 00:00:00' );
					$to_date   = $from->format( 'Y-m-t 23:59:59' );
					return [ $from_date, $to_date ];
				} elseif ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $days[0] ) ) {
					$from = new \DateTime( $days[0], wp_timezone() );
					return [ $from->format( 'Y-m-d 00:00:00' ), $from->format( 'Y-m-d 23:59:59') ];
				} else {
					throw new \Exception( 'Invalid date format.' );
				}
			}
			// 複数の日程がある
			$return = [];
			foreach ( $days as $index => $day ) {
				if ( ! preg_match( '/^\d{4}-\d{2}(-\d{2})?$/', $day, $matches ) ) {
					throw new \Exception( 'Invalid date format.' );
				}
				if ( $matches[1] ) {
					$return[] = $matches[1];
					continue;
				}
				if ( ! $index ) {
					$return[] = $this->to_ymd( $day, '-01' ) . ' 00:00:00';
				} else {
					$to = new \DateTime( $day . '-01', wp_timezone());
					$return []= $to->format( 'Y-m-t 23:59:59' );
				}
			}
			return $return;
		} catch ( \Exception $e ) {
			return new \WP_Error( 'hamail_invalid_filter', __( '日付形式が不正です。', 'hametuha' ) );
		}
	}

	/**
	 * Convert to Y-m-d format.
	 *
	 * @param string $date
	 * @param string $suffix
	 * @return string
	 */
	protected function to_ymd( $date, $suffix = '-01' ) {
		if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
			return $date;
		}
		if ( preg_match( '/^\d{4}-\d{2}$/', $date ) ) {
			return $date . $suffix;
		}
		return $date;
	}
}
