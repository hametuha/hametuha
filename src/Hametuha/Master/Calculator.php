<?php

namespace Hametuha\Master;


use Hametuha\Model\Collaborators;

/**
 * Calculator
 *
 * @package Hametuha\Master
 */
class Calculator {


	/**
	 * 消費税
	 *
	 * @var float
	 */
	const VAT_RATIO = 0.1;

	/**
	 * 源泉徴収
	 *
	 * @var float
	 */
	const DEDUCTING_RATIO = 0.1021;

	/**
	 * Kindleのリワード（70%）に0.715をかけると著者取り分が50%になる
	 *
	 * @var float
	 */
	const BILL_RATIO = 0.715;

	/**
	 * Get currency exchange rate.
	 *
	 * @param string $from
	 * @param string $base
	 * @return float|\WP_Error
	 */
	public static function get_exchange_ratio( $from, $base = 'JPY' ) {
		$from  = strtoupper( $from );
		$base  = strtoupper( $base );
		$key   = "CURRENCY_FROM_{$base}";
		$rates = get_transient( $key );
		if ( false === $rates ) {
			$latest = wp_remote_get( 'https://api.exchangeratesapi.io/latest?base=' . $base );
			if ( is_wp_error( $latest ) ) {
				return $latest;
			}
			$rates = json_decode( $latest['body'], true );
			if ( ! $rates ) {
				return new \WP_Error( 'failed_exchange', __( 'Sorry, but failed to get exchange rate.', 'hametuha' ) );
			}
			set_transient( $key, $rates, 60 * 60 * 24 );
		}
		if ( ! isset( $rates['rates'][ $from ] ) ) {
			return new \WP_Error( 'failed_exchange', __( 'Sorry, but specified currency does not exist in the list.', 'hametuha' ) );
		}
		return 1 / $rates['rates'][ $from ];
	}

	/**
	 * Exchange price to JPY.
	 *
	 * @param float  $price
	 * @param string $currency
	 * @return float
	 */
	public static function exchange( $price, $currency ) {
		$currency = strtoupper( $currency );
		if ( 'JPY' === $currency ) {
			return $price;
		}
		$rate = self::get_exchange_ratio( $currency );
		if ( is_wp_error( $rate ) ) {
			return $price;
		}
		return $price * $rate;
	}


	/**
	 * 消費税などを計算して報酬を登録
	 *
	 * `list($price, $unit, $tax, $deducting, $total) = Calculator::revenue( $price, $unit, true, true )` と使う。
	 *
	 * @param float  $price
	 * @param int    $unit
	 * @param bool   $tax_included_in_price
	 * @param bool   $deduction
	 * @param string $currency Default JPY
	 *
	 * @return array
	 */
	public static function revenue( $price, $unit, $tax_included_in_price = false, $deduction = true, $currency = 'JPY' ) {
		// 消費税と小計を出す
		$sub_total = $unit * $price;
		// 通過が日本でない場合、換算する
		$sub_total = self::exchange( $sub_total, $currency );
		if ( $tax_included_in_price ) {
			$vat        = $sub_total / ( ( self::VAT_RATIO * 100 ) + 100 ) * ( self::VAT_RATIO * 100 );
			$sub_total -= $vat;
		} else {
			$vat = $sub_total * self::VAT_RATIO;
		}
		// 源泉徴収税を出す
		if ( $deduction ) {
			$deduction_price = $sub_total * self::DEDUCTING_RATIO;
		} else {
			$deduction_price = 0;
		}
		// 振込額を出す
		$total = $sub_total - $deduction_price + $vat;
		return [ $price, (int) $unit, $vat, $deduction_price, $total ];
	}

	/**
	 * Returns KDP sales result.
	 *
	 * @param \stdClass $sale
	 * @param string    $prefix
	 * @return array
	 */
	public static function kdp_royalty( $sale, $prefix = '' ) {
		$price       = $sale->sub_total * self::BILL_RATIO / $sale->unit;
		$margin_list = Collaborators::get_instance()->get_final_margin( $sale->post_id );
		if ( ! $margin_list ) {
			return [];
		}
		$series = get_post( $sale->post_id );
		$sales  = [];
		foreach ( $margin_list as $user_id => $margin ) {
			if ( ! $margin ) {
				continue;
			}
			$revenue = $price / 100 * $margin;
			$label   = 100 === $margin ? $prefix : sprintf( '%sの%d%%', $prefix, $margin );
			$sales[] = array_merge( [ $label, $user_id ], self::revenue( $revenue, $sale->unit, true, true, $sale->currency ?: 'JPY' ) );
		}
		return $sales;
	}
}
