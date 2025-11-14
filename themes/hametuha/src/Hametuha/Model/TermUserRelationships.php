<?php

namespace Hametuha\Model;


use WPametu\DB\Model;

/**
 * TermUserRelationships
 *
 * @package Hametuha\Model
 * @property-read string $terms
 * @property-read string $term_taxonomy
 * @property-read string $users
 * @property-read string $posts
 */
class TermUserRelationships extends Model {


	/**
	 * リレーションテーブル名
	 *
	 * @var string
	 */
	protected $name = 'term_user_relationships';

	protected $related = [ 'terms', 'term_taxonomy', 'users', 'posts' ];

	protected $updated_column = 'updated';

	protected $default_placeholder = [
		'user_id'          => '%d',
		'object_id'        => '%d',
		'term_taxonomy_id' => '%d',
	];

	/**
	 * Detect if record exists
	 *
	 * @param int $user_id
	 * @param int $post_id
	 * @param int $term_taxonomy_id
	 * @return bool
	 */
	protected function record_exists( $user_id, $post_id, $term_taxonomy_id ) {
		return (bool) $this->select( 'COUNT(*)' )
			->wheres([
				"{$this->table}.user_id = %d"          => $user_id,
				"{$this->table}.object_id = %d"        => $post_id,
				"{$this->table}.term_taxonomy_id = %d" => $term_taxonomy_id,
			])->get_var();
	}

	/**
	 * Default join
	 *
	 * @return array
	 */
	protected function default_join() {
		return [
			[ $this->term_taxonomy, "{$this->term_taxonomy}.term_taxonomy_id = {$this->table}.term_taxonomy_id", 'inner' ],
			[ $this->terms, "{$this->term_taxonomy}.term_id = {$this->terms}.term_id", 'inner' ],
		];
	}
}
