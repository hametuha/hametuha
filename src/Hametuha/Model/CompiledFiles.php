<?php

namespace Hametuha\Model;


use WPametu\DB\Model;

/**
 * Compiled file list
 *
 * @package Hametuha\Hametuha\Model
 * @property-read string $users
 * @property-read string $posts
 */
class CompiledFiles extends Model
{

	/**
	 * @var string
	 */
	protected $name = 'compiled_files';

	protected $related = ['posts', 'users'];

	protected $updated_column = 'updated';

	protected $default_placeholder = [
		'file_id' => '%d',
		'type' => '%s',
		'post_id' => '%d',
	    'name' => '%s',
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
	public function add_record($type, $post_id, $name){
		return $this->insert([
			'type' => $type,
		    'post_id' => $post_id,
		    'name' => $name,
		]);
	}

	/**
	 * Get file by ID
	 *
	 * @param int $file_id
	 *
	 * @return mixed|null
	 */
	public function get_file($file_id){
		return $this->where("file_id = %d", $file_id)->get_row();
	}

	/**
	 * Delete file
	 *
	 * @param int $file_id
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function delete_file($file_id){
		$file = $this->get_file($file_id);
		if( !$file ){
			return false;
		}
		$path = $this->build_file_path($file);
		$this->delete_where([
			['file_id', '=', $file_id, '%d']
		]);
		if( !unlink($path) ){
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
	 * @return array
	 */
	public function get_files(array $args, $limit = 20, $page = 0){
		$results = [];
		$this->calc()
			->join($this->posts, "{$this->table}.post_id = {$this->posts}.ID", "inner")
			->join($this->users, "{$this->posts}.post_author = {$this->users}.ID", "inner")
			->limit($limit, $page)
			->order_by("{$this->table}.updated", 'DESC');
		$args = wp_parse_args($args, [
			's' => '',
		    'p' => 0,
		    'author' => 0,
		]);
		if( $args['p'] ){
			$this->where("{$this->table}.post_id = %d", $args['p']);
		}
		if( $args['author'] ){
			$this->where("{$this->posts}.post_author = %d", $args['author']);
		}
		if( $args['s'] ){
			$this->where_like("{$this->posts}.post_title", $args['s']);
		}
		foreach( $this->result() as $row ){
			$row->label = $this->type_labels[$row->type];
			$row->path = $this->build_file_path($row);
			$results[] = $row;
		}
		return $results;
	}

	/**
	 * Build file path
	 *
	 * @param \stdClass $file
	 *
	 * @return string
	 */
	public function build_file_path($file){
		return sprintf('%swp-content/hamepub/out/%s/%d/%s', ABSPATH, $file->type, $file->post_id, $file->name);
	}


	/**
	 * Get total result
	 *
	 * @return int
	 */
	public function total(){
		return (int) $this->db->get_var("SELECT FOUND_ROWS()");
	}


	/**
	 * Get compiled objects
	 *
	 * @param int $post_id
	 * @param string $type
	 *
	 * @return int
	 */
	public function record_exists($post_id, $type = ''){
		$this->select('COUNT(file_id)')
			->where("post_id = %d", $post_id);
		if( $type ){
			$this->where("type = %s", $type);
		}
		return (bool) $this->get_var();
	}

}