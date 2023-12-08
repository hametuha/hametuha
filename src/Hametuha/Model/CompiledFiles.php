<?php

namespace Hametuha\Model;


use WPametu\DB\Model;

/**
 * Compiled file list
 *
 * @package hametuha
 * @property-read string $users
 * @property-read string $posts
 * @property-read CompiledFileMeta $meta
 */
class CompiledFiles extends Model {

	/**
	 * @var string
	 */
	protected $name = 'compiled_files';

	protected $related = [ 'posts', 'users' ];

	protected $updated_column = 'updated';

	protected $default_placeholder = [
		'file_id' => '%d',
		'type'    => '%s',
		'post_id' => '%d',
		'name'    => '%s',
		'updated' => '%s',
	];

	protected $type_labels = [
		'kdp' => 'KDP',
	];

	/**
	 * Add record
	 *
	 * @param string $type
	 * @param int $post_id
	 * @param string $name
	 *
	 * @return false|int
	 */
	public function add_record( $type, $post_id, $name ) {
		$result = $this->insert( [
			'type'    => $type,
			'post_id' => $post_id,
			'name'    => $name,
			'updated' => current_time( 'mysql' ),
		] );
			error_log( $this->db->last_query );
		return $result;
	}

	/**
	 * Get file by ID
	 *
	 * @param int $file_id
	 *
	 * @return mixed|null
	 */
	public function get_file( $file_id ) {
		return $this->where( 'file_id = %d', $file_id )->get_row();
	}

	public function get_children_files( $post_id ) {

	}

	/**
	 * Delete all files if post is deleted.
	 *
	 * @param int $post_id
	 * @return int Deleted files count.
	 */
	public function delete_child_files( $post_id ) {
		$deleted = 0;
		foreach ( $this->get_children_files( $post_id ) as $file ) {
			try {

			} catch ( \Exception $e ) {

			}
		}
	}

	/**
	 * Delete file
	 *
	 * @param int $file_id
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function delete_file( $file_id ) {
		$file = $this->get_file( $file_id );
		if ( ! $file ) {
			return false;
		}
		$path = $this->build_file_path( $file );
		$this->delete_where( [
			[ 'file_id', '=', $file_id, '%d' ],
		] );
		$this->meta->delete_where( [
			[ 'file_id', '=', $file_id, '%d' ],
		] );
		if ( ! unlink( $path ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get file list
	 *
	 * @param array $args
	 * @param int $limit
	 * @param int $page
	 *
	 * @return \stdClass[]
	 */
	public function get_files( array $args, $limit = 20, $page = 0 ) {
		$results = [];
		$this->calc()
			 ->join( $this->posts, "{$this->table}.post_id = {$this->posts}.ID", 'left' )
			 ->join( $this->users, "{$this->posts}.post_author = {$this->users}.ID", 'inner' )
			 ->limit( $limit, $page );
		$args = wp_parse_args( $args, [
			's'      => '',
			'p'      => 0,
			'author' => 0,
			'secret' => false,
			'orderby' => 'updated',
			'order'   => 'DESC',
		] );
		// Force value.
		if ( ! in_array( $args['orderby'], [ 'updated' ], true ) ) {
			$args['orderby'] = 'updated';
		}
		if ( ! in_array( $args['order'], [ 'DESC', 'ASC', 'asc', 'desc' ], true ) ) {
			$args['order'] = 'DESC';
		}
		// Set conditions.
		if ( $args['p'] ) {
			$this->where( "{$this->table}.post_id = %d", $args['p'] );
		}
		if ( $args['author'] ) {
			$this->where( "{$this->posts}.post_author = %d", $args['author'] );
		}
		if ( $args['s'] ) {
			$this->where_like( "{$this->posts}.post_title", $args['s'] );
		}
		if ( $args['secret'] ) {
			$this->where( "{$this->posts}.ID IN ( SELECT post_id FROM {$this->db->postmeta} WHERE meta_key = %s AND meta_value = 1 )", '_is_secret_book' );
		}
		$this->order_by( "{$this->table}." . $args['orderby'], $args['order'] );

		return array_map( function( $row ) {
			$row->label = $this->type_labels[ $row->type ];
			$row->path  = $this->build_file_path( $row );
			return $row;
		}, $this->result() );
	}

	/**
	 * Build file path
	 *
	 * @param \stdClass $file
	 *
	 * @return string
	 */
	public function build_file_path( $file ) {
		return sprintf( '%swp-content/hamepub/out/%s/%d/%s', ABSPATH, $file->type, $file->post_id, $file->name );
	}

	/**
	 * Detect if file exists.
	 *
	 * @param int $file_id
	 *
	 * @return \stdClass|null
	 * @throws \Exception
	 */
	public function validate_file( $file_id ) {
		if ( ! ( $file = $this->get_file( $file_id ) ) ) {
			throw new \Exception( '該当するファイルは存在しません。', 404 );
		}
		if ( ! file_exists( ( $path = $this->build_file_path( $file ) ) ) ) {
			throw new \Exception( sprintf( 'ファイルが%sにありません。紛失したようです。', $path ), 404 );
		}

		return $file;
	}

	/**
	 * Validate ePub file.
	 *
	 * @param int $file_id
	 * @return \WP_Error|string[]
	 */
	public function validate( $file_id ) {
		$error = new \WP_Error();
		try {
			// アップロードディレクトリを取得
			$file     = $this->validate_file( $file_id );
			$path     = $this->build_file_path( $file );
			$response = wp_remote_post( 'https://lint.hametuha.pub/validator', [
				'timeout' => 60,
				'body'    => base64_encode( file_get_contents( $path ) ),
			] );
			if ( is_wp_error( $response ) ) {
				return $response;
			}
			if ( ! ( $response = json_decode( $response['body'] ) ) ) {
				throw new \Exception( 'ePubチェックのAPIが動作していません。', 500 );
			}
			if ( $response->success ) {
				return [
					'message' => 'このePubは有効です。',
				];
			} else {
				foreach ( $response->messages as $message ) {
					$error->add( 'epub_failed_validation', $message );
				}
				return $error;
			}
		} catch ( \Exception $e ) {
			$error->add( 'epub_failed_validation', $e->getMessage(), [
				'status' => $e->getCode(),
			] );
			return $error;
		}
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
	 * Download ePub file.
	 *
	 * @param int $file_id
	 * @return \WP_Error
	 */
	public function download( $file_id ) {
		try {
			$file      = $this->validate_file( $file_id );
			$mime_type = 'application/epub+zip';
			$file_path = $this->build_file_path( $file );
			$file_name = get_the_title( $file->post_id ) . '_' . $file->name;
			if ( ! file_exists( $file_path ) ) {
				throw new \Exception( sprintf( '%s にファイルが見つかりませんでした。', $file_path ), 404 );
			}
			set_time_limit( 0 );
			foreach ( array_merge( wp_get_nocache_headers(), [
				'Content-Type'        => $mime_type,
				'Content-Disposition' => sprintf( 'attachment; filename="%s"', $file_name ),
				'Content-Length'      => filesize( $file_path ),
			] ) as $header => $value ) {
				header( "{$header}: {$value}" );
			}
			readfile( $file_path );
			exit;
		} catch ( \Exception $e ) {
			return new \WP_Error( 'failed_to_print_epub', $e->getMessage(), [
				'status' => $e->getCode(),
			] );
		}
	}

	/**
	 * Get published date.
	 *
	 * @param int $post_id
	 * @return \stdClass
	 */
	public function published( $post_id ) {
		return $this
			->select( "{$this->table}.*, {$this->meta->table}.meta_value AS published" )
			->join( $this->meta->table, "{$this->table}.file_id = {$this->meta->table}.file_id AND {$this->meta->table}.meta_key = 'published'", 'innder' )
			->wheres( [
				"{$this->table}.post_id = %d"           => $post_id,
				"{$this->meta->table}.meta_value != %s" => '',
			] )
			->order_by( "{$this->meta->table}.meta_value", 'DESC' )
			->get_row();
	}

	/**
	 * Get compiled objects
	 *
	 * @param int $post_id
	 * @param string $type
	 *
	 * @return int
	 */
	public function record_exists( $post_id, $type = '' ) {
		$this->select( 'COUNT(file_id)' )
			 ->where( 'post_id = %d', $post_id );
		if ( $type ) {
			$this->where( 'type = %s', $type );
		}

		return (bool) $this->get_var();
	}

	/**
	 * Getter
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'meta':
				return CompiledFileMeta::get_instance();
			default:
				return parent::__get( $name );
		}
	}


}
