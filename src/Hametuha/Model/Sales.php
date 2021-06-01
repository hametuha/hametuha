<?php

namespace Hametuha\Model;


use WPametu\DB\Model;

/**
 * Compiled file list
 *
 * @package Hametuha\Hametuha\Model
 */
class Sales extends Model {

	/**
	 * @var string
	 */
	protected $name = 'sales';

	protected $stores = [ 'Amazon' ];

	protected $default_placeholder = [
		'store'    => '%s',
		'date'     => '%s',
		'asin'     => '%s',
		'place'    => '%s',
		'type'     => '%s',
		'unit'     => '%d',
		'royalty'  => '%f',
		'currency' => '%s',
	];

	/**
	 * Insert record
	 *
	 * @param array $value
	 * @param bool|false $skip_validate Default false.
	 *
	 * @return bool|false|int
	 */
	public function add_record( array $value, $skip_validate = false ) {
		if ( ! $skip_validate ) {
			$validate = $this->validate( $value );
			if ( is_wp_error( $validate ) ) {
				return false;
			}
		}

		return $this->insert( $value );
	}

	/**
	 * Get sales report
	 *
	 * @param array $args
	 *
	 * @return array|mixed|null
	 */
	public function get_records( array $args = [] ) {
		$args = wp_parse_args(
			$args,
			[
				'author'   => 0,
				'asin'     => '',
				'store'    => '',
				'type'     => '',
				'per_page' => 20,
				'page'     => 0,
				'year'     => 0,
				'month'    => 0,
			]
		);
		$this->where_not_null( 'pm.meta_value' );
		if ( $args['author'] ) {
			$this->where( 'p.post_author = %d', $args['author'] );
		}
		if ( $args['asin'] ) {
			$this->where( 'pm.meta_value = %s', $args['asin'] );
		}
		if ( $args['type'] ) {
			$this->where( "{$this->table}.type = %s", $args['type'] );
		}
		if ( $args['year'] && $args['month'] ) {
			$this->where( "EXTRACT(YEAR_MONTH FROM {$this->table}.date) = %s", sprintf( '%04d%02d', $args['year'], $args['month'] ) );
		}
		$this->select( "{$this->table}.*, p.*" )
					->calc()
					->order_by( "{$this->table}.date", 'DESC' );
		if ( $args['per_page'] ) {
			$this->limit( $args['per_page'], $args['page'] );
		}
		return $this->result();
	}

	/**
	 * Get detailed result
	 *
	 * @param $from
	 * @param $to
	 * @param int $author_id
	 *
	 * @return array|mixed|null
	 */
	public function get_royalty_report( $from, $to, $author_id = 0 ) {
		if ( $author_id ) {
			$this->where( 'p.post_author = %d', $author_id );
		}
		$rows    = $this->where( "{$this->table}.date BETWEEN %s AND %s", [ $from, $to ] )
						->select( "p.*, {$this->table}.*" )
						->order_by( "{$this->table}.date", 'DESC' )
						->result();
		$results = [];
		$from_ts = strtotime( $from );
		$to_ts   = strtotime( $to );
		$diff    = ceil( ( $to_ts - $from_ts ) / ( 60 * 60 * 24 ) );
		for ( $i = 0; $i <= $diff; $i ++ ) {
			$results[ date_i18n( 'Y-m-d', $from_ts + ( $i * 60 * 60 * 24 ) ) ] = [
				'free'     => 0,
				'paid'     => 0,
				'royalty'  => 0,
				'currency' => 'JPY',
			];
		}
		foreach ( $rows as $row ) {
			if ( isset( $results[ $row->date ] ) ) {
				$results[ $row->date ]['royalty'] += (float) $row->royalty;
				switch ( $row->type ) {
					case '標準':
						$results[ $row->date ]['paid'] += $row->unit;
						break;
					default:
						$results[ $row->date ]['free'] += $row->unit;
						break;
				}
				$results[ $row->date ]['currency'] = $row->currency;
			}
		}

		return $results;
	}

	/**
	 * Get detailed result
	 *
	 * @param $from
	 * @param $to
	 * @param int $author_id
	 *
	 * @return array|mixed|null
	 */
	public function get_title_report( $from, $to, $author_id = 0 ) {
		if ( $author_id ) {
			$this->where( 'p.post_author = %d', $author_id );
		}

		return $this->where( "{$this->table}.date BETWEEN %s AND %s", [ $from, $to ] )
					->select( "p.*, {$this->table}.*" )
					->order_by( "{$this->table}.date", 'DESC' )
					->result();
	}


	protected function default_join() {
		return [
			[ "{$this->db->postmeta} AS pm", "pm.meta_key = '_asin' AND pm.meta_value = {$this->table}.asin", 'left' ],
			[ "{$this->db->posts} AS p", 'p.ID = pm.post_id', 'left' ],
		];
	}


	/**
	 * Validate value
	 *
	 * @param array $value
	 *
	 * @return bool|\WP_Error
	 */
	public function validate( array $value ) {
		foreach ( $this->default_placeholder as $key => $pl ) {
			if ( ! isset( $value[ $key ] ) ) {
				return new \WP_Error( 500, sprintf( '必要な値%sがセットされていません。', $key ) );
			}
			switch ( $pl ) {
				case '%d':
				case '%f':
					if ( ! is_numeric( $value[ $key ] ) ) {
						return new \WP_Error( 500, sprintf( '%sは数字でなければいけません', $key ) );
					}
					break;
				default:
					// Do nothing
					break;
			}
		}

		return true;
	}

	/**
	 * 売上リポートを作成
	 *
	 * @param int    $year
	 * @param int    $month
	 * @param string $day
	 *
	 * @return array|mixed|null
	 */
	public function monthly_report( $year, $month, $day = '01' ) {
		$sales               = UserSales::get_instance();
		list( $start, $end ) = $sales->get_range( $year, $month, $day );
		$result              = $this
			->select( 'p.post_author as user_id, p.post_title as label, p.ID as post_id, s.asin, SUM(s.unit) as unit, SUM(s.royalty) as sub_total, s.currency' )
			->from( "{$this->table} as s" )
			->join( "{$this->db->postmeta} as pm", "pm.meta_key = '_asin' AND pm.meta_value = s.asin" )
			->join( "{$this->db->posts} as p", 'p.ID = pm.post_id' )
			->wheres(
				[
					's.date >= %s'    => $start,
					's.date <= %s'    => $end,
					's.royalty != %d' => 0,
				]
			)
			->group_by( 's.asin, s.currency' )
			->result();

		return array_filter(
			$result,
			function ( $row ) {
				return $row->user_id && ( 0 < $row->sub_total );
			}
		);
	}

	/**
	 * Get total result
	 *
	 * @return int
	 */
	public function total() {
		return (int) $this->db->get_var( 'SELECT FOUND_ROWS()' );
	}

	/**
	 * Get user's guarantee
	 *
	 * @param int $user_id
	 * @param string $type Default 'news'
	 *
	 * @return float
	 */
	public function get_guarantee( $user_id, $type = 'news' ) {
		return (float) get_user_meta( $user_id, "_{$type}_guarantee", true );
	}

	/**
	 * Save user's guarantee
	 *
	 * @param int $user_id
	 * @param $type
	 * @param $value
	 *
	 * @return bool|int
	 */
	public function save_guarantee( $user_id, $type, $value ) {
		return update_user_meta( $user_id, "_{$type}_guarantee", $value );
	}
}
