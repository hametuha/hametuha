<?php

namespace Hametuha\Model;


use WPametu\DB\Model;

/**
 * Class CompiledFileMeta
 *
 * @package hametuha
 * @property-read string $compiled_files
 */
class CompiledFileMeta extends Model {

	protected $name = 'compiled_file_meta';

	protected $related = [ 'compiled_files' ];

	protected $updated_column = 'updated';

	protected $default_placeholder = [
		'meta_id'    => '%d',
		'file_id'    => '%d',
		'meta_key'   => '%s',
		'meta_value' => '%s',
		'created'    => '%s',
	];

	/**
	 * Get single row.
	 *
	 * @param int $file_id
	 * @param string $key
	 * @return \stdClass
	 */
	public function get_meta( $file_id, $key ) {
		return $this->wheres(
			[
				'file_id  = %d' => $file_id,
				'meta_key = %s' => $key,
			]
		)->get_row();
	}

	/**
	 * Get meta array
	 *
	 * @param int    $file_id
	 * @param string $key
	 * @return array
	 */
	public function get_metas( $file_id, $key ) {
		return $this->wheres(
			[
				'file_id'  => $file_id,
				'meta_key' => $key,
			]
		)->result();
	}

	/**
	 * Get all meta
	 *
	 * @param $file_id
	 * @return array|mixed|null
	 */
	public function get_all( $file_id ) {
		return $this->wheres(
			[
				'file_id' => $file_id,
			]
		)->result();
	}

	/**
	 * Delete meta
	 *
	 * @param int $file_id
	 * @param string $key
	 * @return false|int
	 * @throws \Exception
	 */
	public function delete_meta( $file_id, $key ) {
		return $this->delete_where(
			[
				[ 'file_id', '=', $file_id, '%d' ],
				[ 'meta_key', '=', $key, '%s' ],
			]
		);
	}

	/**
	 * Update or add meta.
	 *
	 * @param int    $file_id
	 * @param string $key
	 * @param string $value
	 * @return bool
	 */
	public function update_meta( $file_id, $key, $value ) {
		$existing = $this->get_meta( $file_id, $key );
		if ( $existing ) {
			return (bool) $this->update(
				[
					'meta_value' => $value,
				],
				[
					'file_id'  => $file_id,
					'meta_key' => $key,
				]
			);
		} else {
			return (bool) $this->insert(
				[
					'file_id'    => $file_id,
					'meta_key'   => $key,
					'meta_value' => $value,
					'created'    => current_time( 'mysql' ),
				]
			);
		}
	}
}
