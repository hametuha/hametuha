<?php

namespace Hametuha\Admin\Table;


use Hametuha\Model\CompiledFiles;
use WPametu\Http\Input;

/**
 * Class CompiledFileTable
 *
 * @package Hametuha\Admin\Table
 * @property-read CompiledFiles $files
 * @property-read Input $input
 */
class CompiledFileTable extends \WP_List_Table {


	public function __construct() {
		parent::__construct( [
			'singular' => 'compiled_file',
			'plural'   => 'compiled_files',
			'ajax'     => false,
		] );
	}

	public function get_columns() {
		return [
			'post'    => '対象作品',
			'author'  => '作者',
			'type'    => '種別',
			'file'    => 'ファイル',
			'updated' => '最終更新日',
		];
	}

	/**
	 * @return array
	 */
	function get_sortable_columns() {
		return [
			'updated' => [ 'updated', false ],
		];
	}

	/**
	 * Get a list of CSS classes for the WP_List_Table table tag.
	 *
	 * @since 3.1.0
	 *
	 * @return array List of CSS classes for the table tag.
	 */
	protected function get_table_classes() {
		return [ 'widefat', 'striped', $this->_args['plural'] ];
	}

	/**
	 * Get views
	 *
	 * @return string[]
	 */
	protected function get_views() {
		$author  = $this->input->get( 'author' );
		$post_id = $this->input->get( 'p' );
		if ( ! $author && ! $post_id ) {
			return [];
		}
		$base_url = admin_url( 'edit.php' );
		$args = [
			'post_type' => 'series',
			'page'      => 'hamepub-files',
		];
		foreach ( [ 'orderby', 'order' ] as $key ) {
			$value = $this->input->get( $key );
			if ( $value ) {
				$args[ $key ] = $value;
			}
		}
		$url = add_query_arg( $args, $base_url );
		$views = [
			'all'    => sprintf( '<a href="%s">%s</a>', esc_url( $url ), esc_html__( 'すべて', 'hametuha' ) ),
		];
		if ( $post_id ) {
			$post = get_post( $post_id );
			if ( $post ) {
				$views['author'] = sprintf( '<a href="%s">%s</a>', esc_url( add_query_arg( [ 'author' => $post->post_author ], $base_url ) ), esc_html( get_the_author_meta( 'display_name', $post->post_author ) ) );
				$views['post']   = sprintf( '<a href="#" class="current">%s</a>',  esc_html( get_the_title( $post_id ) ) );
			}
		} elseif ( $author ) {
			$views['author'] = sprintf( '<a href="#" class="current">%s</a>', esc_html( get_the_author_meta( 'display_name', $author ) ) );
		}
		return $views;
	}

	public function prepare_items() {
		//Set column header
		$this->_column_headers = [
			$this->get_columns(),
			[],
			$this->get_sortable_columns(),
		];

		if ( current_user_can( 'edit_others_posts' ) ) {
			$args = [
				's'      => $this->input->get( 's' ),
				'p'      => $this->input->get( 'p' ),
				'author' => $this->input->get( 'author' ),
			];
		} else {
			$args = [
				's'      => $this->input->get( 's' ),
				'p'      => $this->input->get( 'p' ),
				'author' => get_current_user_id(),
			];
		}
		// Set order.
		foreach ( [ 'order', 'orderby' ] as $key ) {
			$value = $this->input->get( $key );
			if ( $value ) {
				$args[ $key ] = $value;
			}
		}
		$this->items = $this->files->get_files( $args, 20, max( 1, $this->get_pagenum() ) - 1 );

		$this->set_pagination_args( [
			'total_items' => $this->files->total(),
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
			case 'type':
				echo $item->label;
				break;
			case 'post':
				printf( '<a href="%s">%s</a>', admin_url( 'edit.php?post_type=series&page=hamepub-files&p=' . $item->post_id ), get_the_title( $item ) );
				$actions = [
					'edit'  => sprintf( '<a href="%s">作品集の編集</a>', admin_url( "post.php?post={$item->post_id}&action=edit" ) ),
					'check' => sprintf( '<a class="compiled-file-validate-btn" href="#" title="%s ePubバリデーション" data-file-id="%d">チェック</a>', get_the_title( $item ), $item->file_id ),
				];
				if ( current_user_can( 'publish_epub', $item->post_id ) ) {
					$updated_date = '';
					$updated      = $this->files->meta->get_meta( $item->file_id, 'published' );
					if ( $updated ) {
						$updated_date = $updated->updated;
					}
					$actions = array_merge( $actions, [
						'download'  => sprintf( '<a class="compiled-file-download-btn" href="%s" target="file-downloader">ダウンロード</a>', add_query_arg( [
							'_wpnonce' => wp_create_nonce( 'wp_rest' ),
						], rest_url( 'hametuha/v1/epub/file/' . $item->file_id ) ) ),
						'delete'    => sprintf( '<a class="compiled-file-delete-btn" href="#" data-file-id="%d"">削除</a>', $item->file_id ),
						'published' => sprintf(
							'<a class="compiled-file-published-btn" href="#" data-file-id="%s" data-published="%s">公開日設定</a>',
							$item->file_id,
							esc_attr( $updated_date )
						),
					] );
				}
				echo $this->row_actions( $actions );
				printf( '<div class="compiled-file-controller"></div>' );
				break;
			case 'author':
				printf( '<a href="%s">%s</a>', admin_url( 'edit.php?post_type=series&page=hamepub-files&author=' . $item->post_author ), esc_html( $item->display_name ) );
				break;
			case 'file':
				if ( file_exists( $item->path ) ) {
					$color = 'green';
					$icon  = 'yes';
					$label = sprintf( '<small>(%sKB)</small>', number_format( filesize( $item->path ) / 1024 ) );
				} else {
					$color = 'red';
					$icon  = 'no';
					$label = '';
				}

				printf(
					'<span style="color: %s"><i class="dashicons dashicons-%s"></i> %s</span> %s',
					$color,
					$icon,
					esc_html( $item->name ),
					$label
				);
				break;
			case 'updated':
				$format  = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
				$updated = $this->files->meta->get_meta( $item->file_id, 'published' );
				printf(
					'%s<br /><small>販売日: <span class="compile-file-published">%s</span></small>',
					mysql2date( $format, $item->updated ),
					$updated ? mysql2date( $format, $updated->meta_value ) : '---'
				);
				break;
		}
	}

	/**
	 * Returns string if nothing found
	 * @return string
	 */
	function no_items() {
		echo '該当するファイルはありません。';
	}

	public function __get( $name ) {
		switch ( $name ) {
			case 'files':
				return CompiledFiles::get_instance();
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
