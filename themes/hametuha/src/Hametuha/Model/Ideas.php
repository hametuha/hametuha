<?php

namespace Hametuha\Model;


use WPametu\DB\Model;


/**
 * Idea table
 *
 * @property-read string $posts
 * @property-read string $users
 * @package Hametuha\Model
 */
class Ideas extends Model {

	protected $updated_column = 'updated';

	protected $name = 'user_content_relationships';

	protected $rel_stock = 'stock';

	protected $related = [
		'posts',
	    'users',
	];

	protected $default_placeholder = [
		'rel_type' => '%s',
		'user_id' => '%d',
		'object_id' => '%d',
		'location' => '%f',
	    'content' => '%s',
	    'updated' => '%s',
	];

	/**
	 * Recommend idea to other
	 *
	 * @param int $user_id
	 * @param int $target_user_id
	 * @param int $idea_id
	 *
	 * @return false|int
	 */
	public function recommend($user_id, $target_user_id, $idea_id) {
		return $this->insert([
			'rel_type' => $this->rel_stock,
			'user_id'  => $target_user_id,
		    'object_id' => $idea_id,
		    'location'  => 0.5,
		    'content'   => (int) $user_id,
		]);
	}

	/**
	 * Deny recommendation
	 *
	 * @param int $user_id
	 * @param int $idea_id
	 *
	 * @return false|int
	 */
	public function trash($user_id, $idea_id) {
		return $this->update([
			'location' => 0,
		], [
			'rel_type'  => $this->rel_stock,
		    'user_id'   => $user_id,
		    'object_id' => $idea_id,
		]);
	}

	/**
	 * Stock content
	 *
	 * @param int $user_id
	 * @param int $idea_id
	 *
	 * @return false|int
	 */
	public function stock($user_id, $idea_id) {
		return $this->insert([
			'rel_type' => $this->rel_stock,
			'user_id'  => $user_id,
			'object_id' => $idea_id,
			'location'  => 1,
		    'content' => 0,
		]);
	}

	/**
	 * Stock again.
	 *
	 * @param int $user_id
	 * @param int $idea_id
	 *
	 * @return false|int
	 */
	public function restock($user_id, $idea_id) {
		return $this->update( [ 'location' => 1 ], [
			'rel_type'  => $this->rel_stock,
			'user_id'   => $user_id,
		    'object_id' => $idea_id,
		]);
	}

	/**
	 * Detect if user stocked this idea
	 *
	 * @param int $user_id
	 * @param int $idea_id
	 *
	 * @return null|int
	 */
	public function is_stocked($user_id, $idea_id) {
		return $this->select( 'ID' )->wheres([
			'rel_type = %s'  => $this->rel_stock,
			'object_id = %d' => $idea_id,
			'user_id = %d'   => $user_id,
		])->get_var();
	}

	/**
	 * Get score to idea
	 *
	 * @param int $user_id
	 * @param int $idea_id
	 *
	 * @return float|null Return null if not stocked, otherwise float.
	 */
	public function score( $user_id, $idea_id ) {
		$location = $this->select( 'location' )->wheres( [
			'rel_type = %s'  => $this->rel_stock,
			'object_id = %d' => $idea_id,
			'user_id = %d'   => $user_id,
		] )->get_var();
		if ( is_null( $location ) ) {
			return null;
		} else {
			return (float) $location ;
		}
	}

	/**
	 * Get stoking list
	 *
	 * @param int $user_id
	 * @param int $offset
	 * @param string $query
	 *
	 * @return array|mixed|null
	 */
	public function get_list( $user_id, $offset, $query = '' ) {
		$sub_query = <<<SQL
			(
				SELECT * FROM {$this->table}
				WHERE rel_type = '{$this->rel_stock}'
				  AND user_id = %d
			) AS r
SQL;
		$sub_query = $this->db->prepare($sub_query, $user_id);
		$this->select( 'p.*, r.user_id AS stocker, r.location, r.content AS recommended_by' )
		     ->calc()
		     ->from( "{$this->db->posts} AS p" )
		     ->join( $sub_query, 'p.ID = r.object_id' )
		     ->where( 'p.post_type = %s', 'ideas' )
		     ->where( '(r.user_id = %d AND r.location > 0 ) OR  (p.post_author = %d)', [ $user_id, $user_id ] )
			 ->order_by( 'COALESCE(r.updated, p.post_date)', 'DESC' )
		     ->limit( 10, $offset / 10 );
		if( $query ){
			$this->where('(p.post_title LIKE %s) OR (p.post_content LIKE %s)', ["%{$query}%", "%{$query}%"]);
		}
		$results = $this->result();
		return [
		    'query'  => $this->db->last_query,
			'total'  => $this->found_count(),
			'offset' => (int) $offset,
			'ideas'  => $results,
		];
	}

	/**
	 * Get stocker list.
	 *
	 * @param int $idea_id
	 * @param int $offset Default 0.
	 * @param int $limit Default 10.
	 * @param bool $include_trash Default false.
	 *
	 * @return array|mixed|null
	 */
	public function get_stockers( $idea_id, $offset = 0, $limit = 10, $include_trash = false ) {
		$this->select( 'u.*' )
		     ->calc()
		     ->from( "{$this->table} AS r" )
		     ->join( "{$this->users} AS u", 'u.ID = r.user_id', 'left' )
		     ->wheres( [
			     'r.rel_type = %s'  => $this->rel_stock,
			     'r.object_id = %d' => $idea_id,
		     ] );
		if ( ! $include_trash ) {
			$this->where( 'r.location = %d', 1 );
		}

		return $this->order_by( 'r.updated', 'DESC' )
		            ->limit( $limit, $offset )
		            ->result();
	}

	/**
	 * Get stocked count
	 *
	 * @param int $idea_id
	 * @param bool $include_trash
	 *
	 * @return int
	 */
	public function get_stock_count( $idea_id, $include_trash = false ) {
		$this->select( 'COUNT(user_id)' )
		     ->wheres( [
			     'rel_type = %s'  => $this->rel_stock,
			     'object_id = %d' => $idea_id,
		     ] );
		if ( ! $include_trash ) {
			$this->where( 'location = %d', 1 );
		}

		return (int) $this->get_var();
	}

	/**
	 * Get stock count
	 *
	 * @param array $idea_ids
	 * @param bool $include_trash
	 *
	 * @return array
	 */
	public function get_stock_list( $idea_ids, $include_trash = false ) {
		$ids = [];
		foreach ( $idea_ids as $idea ) {
			if ( is_numeric( $idea ) ) {
				$ids[] = $idea;
			} elseif ( isset( $idea->ID, $idea->post_type ) && 'ideas' == $idea->post_type ) {
				$ids[] = $idea;
			}
		}
		if ( ! $ids ) {
			return [];
		}
		$this->select( 'object_id AS post_id, COUNT(user_id) AS score' )
		     ->where( 'rel_type = %s', $this->rel_stock )
		     ->where_in( 'object_id', $ids, '%d' )
		     ->group_by( 'object_id' );
		if ( ! $include_trash ) {
			$this->where( 'location = %d', 1 );
		}
		$result = [];
		foreach ( $this->result() as $idea ) {
			$result[ $idea->post_id ] = $idea->score;
		}

		return $result;
	}

	/**
	 * Get total count
	 *
	 * @param bool $include_private
	 *
	 * @return int
	 */
	public function get_total( $include_private = false ) {
		return (int) $this->select( 'COUNT(ID)' )
		                  ->from( $this->posts )
		                  ->where( 'post_type = %s', 'ideas' )
		                  ->where_in( 'post_status', [ 'private', 'publish' ] )
		                  ->get_var();
	}

	/**
	 * Get list of popular tags.
	 *
	 * @param int $limit
	 *
	 * @return array|mixed|null
	 */
	public function popular_tags( $limit = 10 ) {
		$terms = $this->select( 't.*, tt.*, COUNT(r.object_id) AS total' )
		              ->from( "{$this->db->term_relationships} AS r" )
		              ->join( "{$this->db->term_taxonomy} AS tt", 'tt.term_taxonomy_id = r.term_taxonomy_id', 'inner' )
		              ->join( "{$this->db->terms} AS t", 'tt.term_id = t.term_id', 'inner' )
		              ->join( "{$this->db->posts} AS p", 'p.ID = r.object_id' )
		              ->wheres( [
			              'tt.taxonomy = %s' => 'post_tag',
			              'p.post_type = %s' => 'ideas',
		              ] )
		              ->group_by( 'r.term_taxonomy_id' )
		              ->order_by( 'COUNT(r.object_id)', 'DESC' )
		              ->limit( $limit )->result();

		return $terms;
	}

	/**
	 * Get popular ideas
	 *
	 * @param int $limit
	 *
	 * @return array|mixed|null
	 */
	public function popular_ideas( $limit = 10 ) {
		return $this->select( 'p.*, COUNT(r.ID) AS total' )
		            ->from( "{$this->table} AS r" )
		            ->join( "{$this->posts} AS p", 'r.object_id = p.ID' )
		            ->group_by( 'r.object_id' )
		            ->wheres( [
			            'r.rel_type = %s' => $this->rel_stock,
			            'r.location = %d' => 1,
		            ] )
		            ->order_by( 'COUNT(r.ID)', 'DESC' )
		            ->limit( $limit )->result();
	}
}
