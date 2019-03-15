<?php

namespace Hametuha\Master;


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
		return [ $price, $unit, $vat, $deduction_price, $total ];
	}

	/**
	 * Returns KDP sales result.
	 *
	 * @param \stdClass $sale
	 * @return array
	 */
	public static function kdp_royalty( $sale ) {
		$price = $sale->sub_total * self::BILL_RATIO / $sale->unit;
		return self::revenue( $price, $sale->unit, true, true );
	}

}