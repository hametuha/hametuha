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
		$this->join( "{$this->db->postmeta} AS pm", "pm.meta_key = '_asin' AND pm.meta_value = {$this->table}.asin" )
			 ->join( "{$this->db->posts} AS p", 'p.ID = pm.post_id' )
			 ->where_not_null( 'pm.meta_value' );
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