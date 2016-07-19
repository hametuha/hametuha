<?php

namespace Hametuha\Admin\Table;



/**
 * Class CompiledFileTable
 *
 * @package Hametuha\Admin\Table
 */
class UserRewardTable extends RewardTableBase {


	function __construct() {
		parent::__construct( array(
			'singular' => 'user_reward',
			'plural'   => 'user_rewards',
			'ajax'     => false,
		) );
	}

	public function get_columns() {
		return [
			'label'  => '適用',
			'price'  => '売上',
			'total'  => '振込額',
			'status' => '状態',
			'date'   => '日付',
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
		$this->items = $this->user_sales->get_sales_list( [
			'year'     => $this->input->get( 'year' ) ?: date_i18n( 'Y' ),
			'month'    => $this->input->get( 'monthnum' ) ?: date_i18n( 'n' ),
			'status'   => $status ?: 0,
			'type'     => $this->input->get( 'type' ),
			'per_page' => 20,
			'page'     => max( 0, $this->get_pagenum() - 1 ),
		] );
		$this->set_pagination_args( [
			'total_items' => $this->user_sales->found_count(),
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
			case 'label':
				$user = get_userdata( $item->user_id );
				if ( ! $user ) {
					$label = '<span style="color: lightgrey">削除されたユーザー</span>';
				} else {
					$label = sprintf( '<a href="%s">%s</a>', home_url( sprintf( '/doujin/detail/%s/', $user->user_nicename ) ), esc_html( $user->display_name ) );
				}
				printf( '<strong>[%s]</strong> %s -- %s', $this->user_sales->type_label( $item->sales_type ), esc_html( $item->description ), $label );
				break;
			case 'price':
				printf( '&yen; %s &times; %s', number_format_i18n( $item->price, 2 ), number_format_i18n( $item->unit ) );
				break;
			case 'total':
				printf( '&yen; %s', number_format_i18n( $item->{$column_name} ) );
				break;
			case 'unit':
				echo number_format( $item->unit );
				break;
			case 'status':
				echo $this->user_sales->status_label( $item->status );
				break;
			case 'user':
				break;
			case 'date':
				echo mysql2date( get_option( 'date_format' ), $item->created );
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
		<select name="status">
			<?php foreach ( [
			    '1' => '支払い済み',
			    '0' => '未入金',
			    '-1' => '却下',
			] as $val => $label ) :?>
				<option value="<?= esc_attr( $val ) ?>" <?php selected( $val == $status ) ?>>
					<?= esc_html( $label ) ?>
				</option>
			<?php endforeach; ?>
		</select>
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
