<?php

namespace Hametuha\Admin\Table;



/**
 * Class CompiledFileTable
 *
 * @package Hametuha\Admin\Table
 */
class UserBillingTable extends RewardTableBase {


	function __construct() {
		parent::__construct( array(
			'singular' => 'user_billing',
			'plural'   => 'user_billings',
			'ajax'     => false,
		) );
	}

	public function get_columns() {
		return [
			'cb' => '<input type="checkbox" />',
			'payable' => '',
			'user'  => 'ユーザー',
			'account' => '口座',
			'deducting' => '源泉徴収',
			'total'  => '振込額',
		];
	}

	/**
	 * 支払いアクション
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		return [
			'update' => '支払い済みにする',
		];
	}

	/**
	 * アイテムを取得する
	 */
	public function prepare_items() {
		//Set column header
		$this->_column_headers = [
			$this->get_columns(),
			[],
			$this->get_sortable_columns(),
		];
		// ステータスを取得
		$status = $this->input->get( 'status' );
		$this->items = $this->user_sales->get_billing_list(
			$this->input->get( 'year' ) ?: date_i18n( 'Y' ),
			$this->input->get( 'monthnum' ) ?: date_i18n( 'n' )
		);
		$total = $this->user_sales->found_count();
		$this->set_pagination_args( [
			'total_items' => $total,
			'per_page'    => $total,
		] );
	}

	/**
	 * Checkbox column
	 *
	 * @param object $item
	 *
	 * @return string
	 */
	public function column_cb( $item ) {
		return '<input type="checkbox" class="billing-user" name="user_id[]" value="' . esc_attr( $item->user_id ) . '" />';
	}

	/**
	 * Get column
	 *
	 * @param \stdClass $item
	 * @param string $column_name
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'payable':
				if ( hametuha_bank_ready( $item->user_id ) && hametuha_billing_ready( $item->user_id ) ) {
					$color = 'green';
					$icon = 'yes';
				} else {
					$color = 'red';
					$icon = 'no';
				}
				printf( '<i class="dashicons dashicons-%s" style="color: %s"></i>', $icon, $color );
				break;
			case 'user':
				$user = get_userdata( $item->user_id );
				if ( ! $user ) {
					echo '<span style="color: lightgrey">削除されたユーザー</span>';
				} else {
					echo esc_html( $user->display_name );
				}
				break;
			case 'account':
				if ( hametuha_bank_ready( $item->user_id ) ) {
					echo esc_html( implode( ' ', hametuha_bank_account( $item->user_id ) ) );
				} else {
					echo '<span style="color: lightgrey">---</span>';
				}
				break;
			case 'deducting':
			case 'total':
				printf( '&yen; %s', number_format_i18n( round( $item->{$column_name} ) ) );
				break;
		}
	}


	/**
	 * Extra controls to be displayed between bulk actions and pagination
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @param string $which
	 */
	protected function extra_tablenav( $which ) {
		if ( 'top' != $which ) {
			return;
		}
		$year = $this->input->get( 'year' ) ?: date_i18n( 'Y' );
		$month = $this->input->get( 'monthnum' ) ?: date_i18n( 'n' );
		$status = $this->input->get( 'status' );
		?>
		<select name="year">
			<?php for ( $i = (int) date_i18n( 'Y' ); $i >= 2016; $i -- ) : ?>
				<option value="<?= $i ?>"<?php selected( $i == $year ) ?>><?= $i ?>年</option>
			<?php endfor; ?>
		</select>
		<select name="monthnum">
			<?php for ( $i = 1; $i <= 12; $i ++ ) : ?>
				<option value="<?= $i ?>"<?php selected( $i == $month ) ?>><?= $i ?>月
				</option>
			<?php endfor; ?>
		</select>
		<?php
		echo '<input type="submit" class="button" value="フィルター" />';
	}
}
