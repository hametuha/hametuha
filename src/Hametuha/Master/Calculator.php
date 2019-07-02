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
	const VAT_RATIO = 0.08;

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
	 * 消費税などを計算して報酬を登録
	 *
	 * `list($price, $unit, $tax, $deducting, $total) = Calculator::revenue( $price, $unit, true, true )` と使う。
	 *
	 * @param float $price
	 * @param int   $unit
	 * @param bool $tax_included_in_price
	 * @param bool $deduction
	 *
	 * @return array
	 */
	public static function revenue( $price, $unit, $tax_included_in_price = false, $deduction = true ) {
		// 消費税と小計を出す
		$sub_total = $unit * $price;
		if ( $tax_included_in_price ) {
			$vat = $sub_total / ( ( self::VAT_RATIO * 100 ) + 100 ) * ( self::VAT_RATIO * 100 );
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
		$price = $sale->sub_total * self::BILL_RATIO / $sale->unit;
		$margin_list = Collaborators::get_instance()->get_final_margin( $sale->post_id );
		if ( ! $margin_list ) {
			return null;
		}
		$series = get_post( $sale->post_id );
		$sales = [];
		foreach ( $margin_list as $user_id => $margin ) {
			if ( ! $margin ) {
				continue;
			}
			$revenue = $price / 100 * $margin;
			$label   = 100 === $margin ? $prefix : sprintf( '%sの%d%%', $prefix, $margin );
			$sales[] = array_merge( [ $label, $user_id ], self::revenue( $revenue, $sale->unit, true, true ) );
		}
		return $sales;
	}

	public static function group_revenue( $sale ) {

	}

}
