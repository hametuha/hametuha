<?php

namespace Hametuha\Model;


use WPametu\DB\Model;

class UserSales extends Model {
	
	protected $name = 'user_sales';

	/**
	 * 消費税
	 * 
	 * @var float
	 */
	protected $vat_ratio = 0.08;

	/**
	 * 源泉徴収
	 *
	 * @var float
	 */
	protected $deduction_ratio = 0.1021;

	/**
	 * @var float
	 */
	protected $bill = 0.715;
	
	protected $default_placeholder = [
		'sales_id' => '%d',
	    'sales_type' => '%s',
	    'user_id' => '%d',
	    'price' => '%f',
		'unit' => '%f',
		'tax' => '%f',
		'deducting' => '%f',
		'total' => '%f',
		'status' => '%s',
	    'description' => '%s',
	    'created' => '%s',
	    'fixed'   => '%s',
	    'updated' => '%s',
	];

	/**
	 * @var array
	 */
	protected $label = [
		'kdp'  => 'KDP',
	    'task' => '依頼',
	    'news' => 'ニュース',
	];

	/**
	 * 支払いステータスのラベル
	 *
	 * @var array
	 */
	protected $status = [
		0 => '支払い待ち',
	    1 => '支払い済み',
	    -1 => '却下',
	];

	/**
	 * ステータスごとのラベル
	 *
	 * @var array
	 */
	protected  $status_class = [
		0 => 'success',
		1 => 'warning',
		-1 => 'danger',
	];

	protected $updated_column = 'updated';

	/**
	 * ラベルを返す
	 *
	 * @param string $status
	 *
	 * @return string
	 */
	public function status_label( $status ) {
		switch ( $status ) {
			case 1:
			case -1:
				// Do nothing.
				break;
			default:
				$status = 0;
				break;
		}
		return sprintf( '<span class="label label-%s">%s</span>', $this->status_class[ $status ], $this->status[ $status ] );
	}

	/**
	 * 種別のラベルを取得する
	 *
	 * @param string $type
	 *
	 * @return mixed|string
	 */
	public function type_label( $type ) {
		return isset( $this->label[ $type ] ) ? $this->label[ $type ] : '不明';
	}


	/**
	 * セールスを取得する
	 *
	 * @param int $user_id
	 * @param int $year
	 * @param int $month
	 *
	 * @return array
	 */
	public function get_user_sales( $user_id, $year, $month ) {
		list( $start, $end ) = $this->get_range( $year, $month );
		return $this->wheres( [
			'user_id = %d' => $user_id,
			'created >= %s' => $start,
		    'created <= %s' => $end,
		] )->result();
	}

	/**
	 * 月別の売上リストを作成する
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function get_sales_list( $args ) {
		$args = wp_parse_args( $args, [
			'year'  => date_i18n( 'Y' ),
		    'month' => date_i18n( 'm' ),
		    'per_page' => 0,
		    'page' => 0,
		    'status' => null,
		    'type' => false,
		] );
		list( $start, $end ) = $this->get_range( $args['year'], $args['month'] );
		$this->wheres( [
			'created >= %s' => $start,
		    'created <= %s' => $end,
		] );
		if ( ! is_null( $args['status'] ) ) {
			$this->where( 'status = %d', $args['status'] );
		}
		if ( $args['type'] ) {
			$this->where( 'sales_type = %s', $args['type'] );
		}
		if ( $args['per_page'] ) {
			$this->limit( $args['per_page'], $args['page'] );
		}
		return $this->result();
	}

	/**
	 * 支払すべき履歴を返す
	 *
	 * @param int $year
	 * @param int $month
	 *
	 * @return array
	 */
	public function get_billing_list( $year, $month ) {
		$date = new \DateTime();
		$date->setTimezone( new \DateTimeZone( 'Asia/Tokyo' ) );
		$date->setDate( $year, $month, 1 );
		return $this
			->wheres( [
				'created <= %s' => $date->format( 'Y-m-t 23:59:59' ),
		        'status = %d' => 0,
			] )
			->select( 'SUM(total) AS total, user_id, SUM(deducting) AS deducting' )
			->group_by( 'user_id' )
			->result();
	}

	/**
	 * KDPの売上を保存する
	 *
	 * @param int $year
	 * @param int $month
	 * @return array
	 */
	public function save_kdp_report( $year, $month ) {
		$sales = Sales::get_instance()->monthly_report( $year, $month );
		$total = count( $sales );
		$success = 0;
		foreach ( $sales as $sale ) {
			$label = sprintf( '%d年%d月『%s』', $year, $month, $sale->label );
			if ( $this->add( $sale->user_id, 'kdp', $sale->sub_total * $this->bill / $sale->unit, $sale->unit, $label, true ) ) {
				$success++;
			}
		}
		return [ $total, $success ];
	}



	/**
	 * データを挿入する
	 *
	 * @param int $user_id
	 * @param string $type
	 * @param float $price
	 * @param int $unit
	 * @param string $description
	 * @param bool $tax_included
	 * @param bool $deduction
	 * @param int $status
	 *
	 * @return false|int
	 */
	public function add( $user_id, $type, $price, $unit = 1, $description = '', $tax_included = false, $deduction = true, $status = 0 ) {
		// 消費税と小計を出す
		$sub_total = $unit * $price;
		if ( $tax_included ) {
			$vat = $sub_total / ( ( $this->vat_ratio * 100 ) + 100 ) * ( $this->vat_ratio * 100 );
			$sub_total -= $vat;
		} else {
			$vat = $sub_total * $this->vat_ratio;
		}
		// 源泉徴収税を出す
		if ( $deduction ) {
			$deduction_price = $sub_total * $this->deduction_ratio;
		} else {
			$deduction_price = 0;
		}
		// 振込額を出す
		$total = $sub_total - $deduction_price + $vat;
		// 保存する
		return $this->insert( [
			'user_id' => $user_id,
		    'sales_type' => $type,
		    'price' => $price,
		    'unit'  => $unit,
		    'tax' => $vat,
		    'deducting' => $deduction_price,
		    'total' => $total,
		    'status' => $status,
		    'description' => $description,
		    'created' => current_time( 'mysql' ),
		] );
	}

	/**
	 * 支払いを更新する
	 *
	 * @param int $sales_id
	 * @param int $status
	 *
	 * @return false|int
	 */
	public function update_status( $sales_id, $status ) {
		return $this->update( [
			'sales_id' => $sales_id,
		    'status'   => $status,
		] );
	}
	
	/**
	 * 月初と月末を取得する
	 *
	 * @param int $year
	 * @param int $month
	 * @return array
	 */
	public function get_range( $year, $month ) {
		$start = sprintf( '%04d-%02d-01 00:00:00', $year, $month );
		$d = new \DateTime();
		$d->setTimezone( new \DateTimeZone( 'Asia/Tokyo' ) );
		$d->setDate( $year, $month, 1 );
		$end = $d->format( 'Y-m-t 23:59:59' );
		return [ $start, $end ];
	}
}