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
			'type'    => '種別',
			'post'    => '対象作品',
			'author'  => '作者',
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
			    'secret' => true,
			];
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
				echo $this->row_actions( [
					'edit'     => sprintf( '<a href="%s">編集</a>', admin_url( "post.php?post={$item->post_id}&action=edit" ) ),
					'check'    => sprintf( '<a href="%s" class="thickbox" title="%s ePubバリデーション">チェック</a>', home_url( "epub/check/{$item->file_id}", 'https' ), get_the_title( $item ) ),
					'download' => sprintf( '<a href="%s" target="file-downloader">ダウンロード</a>', home_url( 'epub/file/' . $item->file_id, 'https' ) ),
					'delete'   => sprintf( '<a href="%s" onclick="return confirm(\'本当に削除してよろしいですか？　この操作は取り消せません。\');">削除</a>', home_url( 'epub/delete/' . $item->file_id, 'https' ) ),
				] );
				break;
			case 'post':
				printf( '<a href="%s">%s</a>', admin_url( 'edit.php?post_type=series&page=hamepub-files&p=' . $item->post_id ), get_the_title( $item ) );
				break;
			case 'author':
				printf( '<a href="%s">%s</a>', admin_url( 'edit.php?post_type=series&page=hamepub-files&author=' . $item->post_author ), esc_html( $item->display_name ) );
				break;
			case 'file':
				echo esc_html( $item->name );
				if ( file_exists( $item->path ) ) {
					printf( '　<small style="color: green;"><span class="dashicons dashicons-yes"></span> (%sKB)</small>', number_format( filesize( $item->path ) / 1024 ) );
				} else {
					echo '　<small style="color: red;"><span class="dashicons dashicons-no"></span> ファイルなし</small>';
				}
				break;
			case 'updated':
				echo mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $item->updated );
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
