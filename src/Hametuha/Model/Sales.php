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
	public function get_records( array $args = [ ] ) {
		$args = wp_parse_args( $args, [
			'author' => 0,
			'asin'   => '',
			'store'  => '',
			'type'   => '',
		] );
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

		return $this->select( "{$this->table}.*, p.*" )
					->calc()
					->order_by( "{$this->table}.date", 'DESC' )
					->result();
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
	 * Get total result
	 *
	 * @return int
	 */
	public function total() {
		return (int) $this->db->get_var( 'SELECT FOUND_ROWS()' );
	}
}