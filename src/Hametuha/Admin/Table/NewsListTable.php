<?php

namespace Hametuha\Admin\Table;

use Hametuha\Model\Author;
use WPametu\Http\Input;

/**
 * News list table to check pv
 *
 * @package Hametuha\Admin\Table
 * @property-read Input $input
 * @property-read Author $authors
 */
class NewsListTable extends \WP_List_Table {

	public function __construct() {
		parent::__construct(
			[
				'singular' => 'news',
				'plural'   => 'news',
				'ajax'     => false,
			]
		);
	}

	public function get_columns() {
		return [
			'title'  => 'タイトル',
			'date'   => '公開日',
			'author' => '執筆者',
			'genre'  => 'カテゴリー',
			'pv'     => 'ページビュー',
		];
	}

	/**
	 * @return array
	 */
	function get_sortable_columns() {
		return [
			'date' => [ 'date', 'DESC' ],
			'pv'   => [ 'pv', false ],
		];
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

		if ( current_user_can( 'edit_others_posts' ) ) {
			$authors = $this->authors->get_journalists();
			?>
			<select name="author">
				<option value=""<?php selected( ! $this->input->get( 'author' ) ); ?>>すべての投稿者</option>
				<?php foreach ( $authors as $user ) : ?>
					<option value="<?php echo $user->ID; ?>"<?php selected( $this->input->get( 'author' ), $user->ID ); ?>>
						<?php echo esc_html( $user->display_name ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<select name="news-year">
				<option value="0"<?php selected( ! $this->input->get( 'news-year' ) ); ?>>すべての年</option>
				<?php for ( $i = (int) date_i18n( 'Y' ); $i >= 2016; $i-- ) : ?>
					<option value="<?php echo $i; ?>"<?php selected( $i == $this->input->get( 'news-year' ) ); ?>><?php echo $i; ?>年</option>
				<?php endfor; ?>
			</select>
			<select name="news-month">
				<option value="0"<?php selected( ! $this->input->get( 'news-month' ) ); ?>>すべての月</option>
				<?php for ( $i = 1; $i <= 12; $i++ ) : ?>
					<option value="<?php echo $i; ?>"<?php selected( $i == $this->input->get( 'news-month' ) ); ?>><?php echo $i; ?>月</option>
				<?php endfor; ?>
			</select>
			<?php
		}
		echo '<input type="submit" class="button" value="フィルター" />';
	}


	public function prepare_items() {
		//Set column header
		$this->_column_headers = [
			$this->get_columns(),
			[],
			$this->get_sortable_columns(),
		];
		// Build Query
		$args = [
			'post_type'      => 'news',
			'post_status'    => 'publish',
			'posts_per_page' => 20,
			'paged'          => max( 1, $this->get_pagenum() ),
			's'              => $this->input->get( 's' ),
			'tax_query'      => [],
			'meta_query'     => [],
		];
		// 投稿者
		if ( current_user_can( 'edit_others_posts' ) ) {
			$args['author'] = $this->input->get( 'author' );
		} else {
			$args['author'] = get_current_user_id();
		}
		// タクソノミー
		if ( $term_id = $this->input->get( 'genre' ) ) {
			$args['tax_query'][] = [
				'taxonomy' => 'genre',
				'terms'    => $term_id,
				'field'    => 'id',
			];
		}
		// 公開日による絞込
		$year  = $this->input->get( 'news-year' );
		$month = $this->input->get( 'news-month' );
		if ( $year && $month ) {
			$start = sprintf( '%04d-%02d-01 00:00:00', $year, $month );
			$d     = new \DateTime();
			$d->setTimezone( new \DateTimeZone( 'Asia/Tokyo' ) );
			$d->setDate( $year, $month, 1 );
			$end                  = $d->format( 'Y-m-t 23:59:59' );
			$args['meta_query'][] = [
				'key'     => '_news_published',
				'value'   => [ $start, $end ],
				'compare' => 'BETWEEN',
				'type'    => 'DATETIME',
			];
		}
		// 並び順
		switch ( $this->input->get( 'orderby' ) ) {
			case 'pv':
				$args['meta_key'] = '_current_pv';
				$args['orderby']  = 'meta_value_num';
				$args['order']    = $this->input->get( 'order' );
				break;
			default:
				// Do nothign
				$args['orderby'] = 'date';
				$args['order']   = $this->input->get( 'order' );
				break;
		}

		$query = new \WP_Query( $args );

		$this->items = $query->posts;

		$this->set_pagination_args(
			[
				'total_items' => $query->found_posts,
				'per_page'    => 20,
			]
		);
	}

	/**
	 * Get column
	 *
	 * @param \WP_Post $post
	 * @param string $column_name
	 */
	public function column_default( $post, $column_name ) {
		switch ( $column_name ) {
			case 'title':
				echo get_the_title( $post );
				break;
			case 'author':
				printf(
					'<a href="%s">%s</a>',
					admin_url( 'edit.php?post_type=news&page=hamenew-score&author=' . $post->post_author ),
					get_the_author_meta( 'display_name', $post->post_author )
				);
				break;
			case 'genre':
				$terms = get_the_terms( $post, 'genre' );
				if ( ! $terms || is_wp_error( $terms ) ) {
					echo '<span style="color: lightgrey">---</span>';
				} else {
					echo implode(
						', ',
						array_map(
							function( $term ) {
								return sprintf(
									'<a href="%s">%s</a>',
									admin_url( 'edit.php?post_type=news&page=hamenew-score&genre=' . $term->term_id ),
									esc_html( $term->name )
								);
							},
							$terms
						)
					);
				}
				break;
			case 'date':
				echo get_the_time( get_option( 'date_format' ), $post );
				break;
			case 'pv':
				$pv = (int) get_post_meta( $post->ID, '_current_pv', true );
				echo number_format( $pv );
				break;
			default:
				// Do nothing.
				break;
		}
	}


	/**
	 * Returns string if nothing found
	 *
	 * @return string
	 */
	function no_items() {
		echo '該当するニュースはありません';
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
			case 'input':
				return Input::get_instance();
				break;
			case 'authors':
				return Author::get_instance();
				break;
			default:
				return parent::__get( $name );
				break;
		}
	}
}
