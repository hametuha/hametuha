<?php

namespace Hametuha\Model;


use Hametuha\Master\Calculator;
use Hametuha\Sharee\Models\RevenueModel;
use WPametu\DB\Model;

/**
 * Class UserSales
 *
 * @package hametuha
 *
 */
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
		0  => 'success',
		1  => 'warning',
		-1 => 'danger',
	];

	protected $updated_column = 'updated';

	/**
	 * ラベルを返す
	 *
	 * @deprecated
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
	 * @deprecated
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
	 * @deprecated
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
	 * 確定済みの報酬を表示する
	 *
	 * @deprecated
	 * @param int $user_id
	 * @param int $year
	 * @return array
	 */
	public function get_fixed( $user_id, $year = 0 ) {
		$this->select( 'user_id, SUM(unit) AS unit, SUM(deducting) AS deducting, SUM(total) AS total, EXTRACT(YEAR_MONTH FROM fixed) as payed' )
			->wheres( [
				'user_id = %d' => $user_id,
			    'status = %d' => 1,
			] )
			->group_by( 'payed', 'DESC' );
		if ( $year ) {
			$this->where( ' EXTRACT(YEAR from fixed ) = %d', $year );
		}
		return $this->result();
	}

	/**
	 * 月別の売上リストを作成する
	 *
	 * @deprecated
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
	 * @deprecated
	 * @param int $year
	 * @param int $month
	 * @param int $user_id
	 * @param int|array $status
	 * @param bool $range If true, only on this month.
	 *
	 * @return array
	 */
	public function get_billing_list( $year, $month, $user_id = 0, $status = 0, $range = true ) {
		$date = new \DateTime();
		$date->setTimezone( new \DateTimeZone( 'Asia/Tokyo' ) );
		$date->setDate( $year, $month, 1 );
		if ( $range ) {
			$this->where( 'created BETWEEN %s AND %s', [ $date->format( 'Y-m-01 00:00:00' ), $date->format( 'Y-m-t 23:59:59' ) ] );
		} else {
			$this->where( 'created <= %s', $date->format( 'Y-m-t 23:59:59' ) );
		}
		if ( is_array( $status ) ) {
			$this->where_in( 'status', $status, '%d' );
		} else {
		    $this->where( 'status = %d', $status );
		}
		if ( $user_id ) {
			$this->select( 'sales_id, total, user_id, deducting, description, created, fixed, unit, sales_type, status' )
				->where( 'user_id = %d', $user_id )
				->order_by( 'created', 'DESC' );
		} else {
			$this->select( 'SUM(total) AS total, user_id, SUM(deducting) AS deducting' )
				->group_by( 'user_id' );
		}
		return $this->result();
	}

	/**
	 * 支払い済みの履歴を返す
	 *
	 * @deprecated
	 * @param string $year
	 * @param string $month
	 *
	 * @return array|mixed|null
	 */
	public function get_fixed_billing( $year, $month ) {
		$year_month = sprintf( '%04d%02d', $year, $month );
		return $this
			->select( "SUM({$this->table}.total) AS total, SUM({$this->table}.deducting) AS deducting, {$this->table}.fixed, {$this->table}.user_id, {$this->db->users}.display_name" )
			->join( $this->db->users, "{$this->db->users}.ID = {$this->table}.user_id" )
			->wheres( [
				"EXTRACT(YEAR_MONTH FROM {$this->table}.`fixed`) = %s" => $year.$month,
		        "{$this->table}.`status` = %d" => 1,
			] )
			->order_by( "{$this->db->users}.ID", 'ASC' )
			->result();
	}

	/**
	 * Get payment history
	 *
	 * @deprecated
	 * @param int $year
	 * @param int $user_id
	 * @return array
	 */
	public function get_payments_list( $year, $user_id = 0 ) {
		$this
			->select( "SUM({$this->table}.total) AS total, SUM({$this->table}.deducting) AS deducting, {$this->table}.user_id, {$this->db->users}.display_name" )
			->join( $this->db->users, "{$this->db->users}.ID = {$this->table}.user_id" )
			->wheres( [
				"EXTRACT(YEAR FROM {$this->table}.`fixed`) = %s" => $year,
		        "{$this->table}.`status` = %d" => 1,
			] )
			->group_by( "{$this->table}.user_id", 'ASC' )
			->group_by( "{$this->table}.fixed", 'DESC' )
			->order_by( "{$this->table}.fixed", 'ASC' );
		if ( $user_id ) {
			$this->where( "{$this->table}.user_id = %d", $user_id );
		}
		return $this->result();
	}

	/**
	 * KDPの売上を保存する
	 *
	 * @param int  $year
	 * @param int  $month
	 * @param bool $dry_run If set true, returns array to be inserted.
	 * @return array
	 */
	public function save_kdp_report( $year, $month, $dry_run = false ) {
		$sales     = Sales::get_instance()->monthly_report( $year, $month );
		$retrieved = 0;
		$success   = 0;
		$return    = [];
		foreach ( $sales as $sale ) {
			$prefix = sprintf( '%d年%d月『%s』', $year, $month, $sale->label );
			if ( 'JPY' !== $sale->currency ) {
				$prefix .= sprintf( '（売上%s%s）', number_format( $sale->sub_total, 2 ), $sale->currency );
			}
			$created = date_i18n( 'Y-m-d H:i:s', strtotime( sprintf( '%04d-%02d-15 00:00:00', $year, $month ) . ' + 1 month' ) );
			$royalties = Calculator::kdp_royalty( $sale, $prefix );
			// Calculate price for collaborators.
			if ( $dry_run ) {
				foreach ( $royalties as $royalty ) {
					list( $label, $user_id, $price, $unit, $tax, $deducting, $total ) = $royalty;
					$return[] = (object) [
						'label'     => $label,
						'user_id'   => $user_id,
						'unit'      => $unit,
						'total'     => $total,
						'deducting' => $deducting,
						'currency'  => $sale->currency,
					];
				}
			} else {
				// Actually save reports.
				foreach ( $royalties as $royalty ) {
					$retrieved++;
					list( $label, $user_id, $price, $unit, $tax, $deducting, $total ) = $royalty;
					$result = RevenueModel::get_instance()->add_revenue( 'kdp', $user_id, $price, [
						'unit'        => $unit,
						'total'       => $total,
						'tax'         => $tax,
						'deducting'   => $deducting,
						'description' => $label,
						'currency'  => $sale->currency,
					] );
					if ( $result && !is_wp_error( $result ) ) {
						$success++;
					}
				}
			}
		}
		return $dry_run ? $return : [ $retrieved, $success ];
	}

	/**
	 *
	 *
	 * @param int $year
	 * @param int $month
	 *
	 * @return array
	 */
	public function save_news_report( $year, $month ) {
		$done = 0;
		$none = 0;
		foreach ( $this->get_news_report( $year, $month ) as $user_id => $sales ) {
			if ( $sales['total'] ) {
				$label = sprintf( '%d年%d月 ニュース %d/%d 記事', $year, $month, $sales['valid'], $sales['count'] );
				$created = date_i18n( 'Y-m-d H:i:s', strtotime( sprintf( '%04d-%02d-15 00:00:00', $year, $month ) . ' + 1 month' ) );
				list( $price, $unit, $tax, $deducting, $total ) = Calculator::revenue( $sales['total'] / $sales['valid'], $sales['valid'], true, true );
				$result = RevenueModel::get_instance()->add_revenue( 'news', $user_id, $price, [
					'unit'        => $unit,
					'total'       => $total,
					'deducting'   => $deducting,
					'description' => $label,
					'created'     => $created,
					'tax'         => $tax,
				] );
				if ( $result && ! is_wp_error( $result ) ) {
					$done++;
				}
			} else {
				$none++;
			}
		}
		return [ $done, $none ];
	}

	/**
	 * Get news release.
	 *
	 * @param int $year
	 * @param int $month
	 *
	 * @return array
	 */
	public function get_news_report( $year, $month ) {
		list( $start, $end ) = $this->get_range( $year, $month );
		$result = [];
		foreach (
			get_posts( [
				'post_type'      => 'news',
				'post_status'    => 'publish',
				'posts_per_page' => - 1,
				'meta_query'     => [
					[
						'key'     => '_news_published',
						'value'   => [ $start, $end ],
						'compare' => 'BETWEEN',
						'type'    => 'DATETIME',
					],
				],
			] ) as $post
		) {
			if ( ! isset( $result[ $post->post_author ] ) ) {
				$result[ $post->post_author ] = [
					'count' => 0,
					'valid' => 0,
					'total' => 0,
				];
			}
			$result[ $post->post_author ]['count'] ++;
			$get = 0;
			if ( $guarantee = Sales::get_instance()->get_guarantee( $post->post_author, 'news' ) ) {
				$get = $guarantee;
				$result[ $post->post_author ]['valid'] ++;
			} else {
				if (  2000 < get_post_meta( $post->ID, '_current_pv', true ) ) {
					$get = 500;
					$result[ $post->post_author ]['valid'] ++;
				}
			}
			$result[ $post->post_author ]['total'] += $get;
		}
		return $result;
	}

	/**
	 * データを挿入する
	 *
	 * @deprecated
	 * @param int $user_id
	 * @param string $type
	 * @param float $price
	 * @param int $unit
	 * @param string $description
	 * @param bool $tax_included
	 * @param bool $deduction
	 * @param int $status
	 * @param string $created
	 *
	 * @return false|int
	 */
	public function add( $user_id, $type, $price, $unit = 1, $description = '', $tax_included = false, $deduction = true, $status = 0, $created = '' ) {

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
		    'created' => $created ?: current_time( 'mysql' ),
		    'updated' => current_time( 'mysql' ),
		] );
	}

	/**
	 * Get list of my number.
	 *
	 * @param int $year Year
	 *
	 * @return array
	 */
	public function get_my_numbers( $year ) {
		$users = $this->select( 'u.*, SUM( s.total ) AS amount' )
			->from( "{$this->db->users} AS u" )
			->join( "{$this->table} AS s", 'u.ID = s.user_id' )
			->wheres( [
				'EXTRACT( YEAR FROM s.fixed ) = %d' => $year,
			] )
			->group_by( 'u.ID' )
			->result();
		$user_ids = [];
		foreach ( $users as $user ) {
			$user_ids[] = $user->ID;
		}
		// Get user meta
		$metas = $this->select( '*' )
			->from( $this->db->usermeta )
			->where_in( 'user_id', $user_ids, '%d' )
			->where_in( 'meta_key', [ '_billing_name', '_billing_number', '_billing_address' ] )
			->result();
		return array_map( function( $user ) use ( $metas ) {
			$user->my_number = '';
			$user->address   = '';
			foreach ( $metas as $row ) {
				if ( $row->user_id != $user->ID ) {
					continue;
				}
				switch ( $row->meta_key ) {
					case '_billing_name';
						$user->display_name = $row->meta_value;
						break;
					case '_billing_number':
						$user->my_number = $row->meta_value;
						break;
					case '_billing_address':
						$user->address = $row->meta_value;
						break;
					default:
						// Do nothing.
						break;
				}
			}
			return $user;
		}, $users );
	}

	/**
	 * 支払いを確定する
	 *
	 * @deprecated
	 * @param array|int $user_ids
	 *
	 * @return false|int
	 */
	public function fix_billing( $user_ids = [] ) {
		if ( is_numeric( $user_ids ) ) {
			$user_ids = (array) $user_ids;
		}
		$user_ids = implode( ', ', array_map( 'intval', array_unique( $user_ids ) ) );
		if ( ! $user_ids ) {
			return false;
		}
		$query = <<<SQL
			UPDATE {$this->table}
			SET `fixed` = %s,
                `updated` = %s,
                `status` = 1
            WHERE `user_id` IN ( {$user_ids} )
              AND `status` = 0
SQL;
		$now = current_time( 'mysql' );
		return $this->db->query( $this->db->prepare( $query, $now, $now ) );
	}

	/**
	 * 支払いを更新する
	 *
	 * @deprecated
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
	 * @deprecated
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