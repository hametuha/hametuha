<?php

namespace Hametuha\Admin\Table;


use Hametuha\Model\Sales;
use WPametu\Http\Input;

/**
 * Class CompiledFileTable
 *
 * @package Hametuha\Admin\Table
 * @property-read Sales $sales
 * @property-read Input $input
 */
class SalesReportTable extends \WP_List_Table {


	function __construct() {
		parent::__construct( array(
			'singular' => 'sales_report',
			'plural'   => 'sales_reports',
			'ajax'     => false,
		) );
	}

	public function get_columns() {
		return [
			'post'    => '作品',
			'author'  => '作者',
			'store'   => 'ストア',
			'date'    => '日付',
			'type'    => '種別',
			'unit'    => '販売数',
			'royalty' => 'ロイヤリティ総額',
		];
	}

	/**
	 * @return array
	 */
	function get_sortable_columns() {
		return [
			'date' => [ 'date', true ],
		];
	}

	public function prepare_items() {
		//Set column header
		$this->_column_headers = [
			$this->get_columns(),
			[],
			$this->get_sortable_columns(),
		];

		$this->items = $this->sales->get_records( [
			'per_page' => 20,
			'page'   => max( 0, $this->get_pagenum() - 1 ),
		] );

		$this->set_pagination_args( [
			'total_items' => $this->sales->total(),
			'per_page'    => 20,
		] );
	}

	/**
	 * Get column
	 *
	 * @param \stdClass $item
	 * @param string $column_name
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'store':
			case 'type':
				echo esc_html( $item->{$column_name} );
				break;
			case 'royalty':
				printf( '%s <small>%s</small>', number_format( $item->royalty, 2 ), $item->currency );
				break;
			case 'unit':
				echo number_format( $item->unit );
				break;
			case 'author':
				printf( '<a href="%s">%s</a>', get_author_posts_url( $item->post_author ), esc_html( get_the_author_meta( 'display_name', $item->post_author ) ) );
				break;
			case 'post':
				echo get_the_title( $item );
				break;
			case 'date':
				echo mysql2date( get_option( 'date_format' ), $item->date );
				break;
		}
	}


	/**
	 * Returns string if nothing found
	 * @return string
	 */
	function no_items() {
		echo '売上がありません';
	}

	/**
	 * Getter
	 *
	 * @param string $name
	 *
	 * @return mixed|static
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'sales':
				return Sales::get_instance();
				break;
			case 'input':
				return Input::get_instance();
				break;
			default:
				return parent::__get( $name );
				break;
		}
	}
}
