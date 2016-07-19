<?php

namespace Hametuha\Admin\Table;


use Hametuha\Model\UserSales;
use WPametu\Http\Input;

/**
 * Class RewardTableBase
 * @package Hametuha\Admin\Table
 * @property-read UserSales $user_sales
 * @property-read Input $input
 */
abstract class RewardTableBase extends \WP_List_Table {



	/**
	 * Returns string if nothing found
	 * @return string
	 */
	function no_items() {
		echo '表示するデータがありません';
	}

	/**
	 * Get a list of CSS classes for the list table table tag.
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @return array List of CSS classes for the table tag.
	 */
	protected function get_table_classes() {
		return array( 'widefat', 'striped', $this->_args['plural'] );
	}


	/**
	 * Getter
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'user_sales':
				return UserSales::get_instance();
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
