<?php

namespace Hametuha\Model;


use WPametu\DB\Model;

/**
 * Follower model
 *
 * @package Hametuha\Model
 * @property-read string $users
 * @property-read string $usermeta
 */
class Follower extends Model
{

	protected $name = 'user_relationships';

	protected $related = [ 'users', 'usermeta' ];

	protected $updated_column = 'updated';

	/**
	 * @var array
	 */
	protected $default_placeholder = [
		'ID' => '%d',
		'user_id' => '%s',
		'target_id' => '%d',
		'status' => '%d',
		'created' => '%s',
		'updated' => '%s',
	];


	/**
	 * Follow user
	 *
	 * @param int $user_id
	 * @param int $target_id
	 *
	 * @return bool|\WP_Error
	 */
	public function follow( $user_id, $target_id ) {
		if ( $this->is_blocked( $user_id, $target_id )
		     || ! $this->insert( [
					'user_id'   => $user_id,
					'target_id' => $target_id,
					'created'   => current_time( 'mysql' ),
				] )
		) {
			return new \WP_Error( 500, 'フォローできませんでした。' );
		} else {
			return true;
		}
	}

	/**
	 * Unfollow user.
	 *
	 * @param int $user_id
	 * @param int $target_id
	 *
	 * @return bool|\WP_Error
	 * @throws \Exception
	 */
	public function unfollow( $user_id, $target_id ) {
		if ( $this->delete_where( [
				[ 'user_id', '=', $user_id, '%d' ],
				[ 'target_id', '=', $target_id, '%d' ],
		] )
		) {
			return true;
		} else {
			return new \WP_Error( 500, 'フォローを解除できませんでした。' );
		}
	}

	/**
	 * Detect if user follows target
	 *
	 * @param int $user_id
	 * @param int $target_id
	 *
	 * @return bool
	 */
	public function is_following( $user_id, $target_id ) {
		return (bool) $this->select( 'target_id' )
		                   ->wheres( [
				                   'user_id = %d'   => $user_id,
				                   'target_id = %d' => $target_id,
		                   ] )->get_var();
	}

	/**
	 * Get followers
	 *
	 * @param int $user_id
	 * @param int $offset
	 * @return array
	 */
	public function get_followers( $user_id, $offset = 0 ) {
		$result          = [
				'total'  => 0,
				'offset' => $offset,
				'users'  => [],
		];
		$sub_query = <<<SQL
			(
				SELECT target_id, 1 AS following
				FROM {$this->table} WHERE user_id = %d
			) AS r2
SQL;
		$sub_query = $this->db->prepare($sub_query, $user_id);
		$result['users'] = $this->calc()->select( 'u.*, r2.following' )
		                        ->join( "$this->users AS u", "u.ID = {$this->table}.user_id", 'INNER' )
								->join( $sub_query, "u.ID = r2.target_id", 'LEFT' )
		                        ->wheres( [
				                        "{$this->table}.target_id = %d" => $user_id,
				                        "{$this->table}.status = %d"    => 1,
		                        ] )
		                        ->order_by( "{$this->table}.updated", 'DESC' )
		                        ->limit( 20, $offset )
								->result();
		$result['total'] = $this->found_count();
		return $result;
	}

	/**
	 * Get followings
	 *
	 * @param int $user_id
	 * @param int $offset
	 * @return array
	 */
	public function get_following( $user_id, $offset = 0 ) {
		$result          = [
				'total'  => 0,
				'offset' => $offset,
				'users'  => [],
		];
		$result['users'] = $this->calc()->select( 'u.*' )
		                        ->join( "$this->users AS u", "u.ID = {$this->table}.target_id", 'INNER' )
		                        ->wheres( [
				                        "{$this->table}.user_id = %d" => $user_id,
				                        "{$this->table}.status = %d"    => 1,
		                        ] )
		                        ->order_by( "{$this->table}.updated", 'DESC' )
		                        ->limit( 20, $offset )
		                        ->result();
		$result['total'] = $this->found_count();
		return $result;
	}

	/**
	 * Detect if user is blocked.
	 *
	 * @param int $user_id
	 * @param int $blocker_id
	 *
	 * @return bool
	 */
	public function is_blocked( $user_id, $blocker_id ) {
		return (bool) $this->select( 'target_id' )
		                   ->wheres( [
			                   'user_id = %d' => $blocker_id,
			                   'target_id = %d'    => $user_id,
			                   'status = %d'       => 0,
		                   ] )->get_var();
	}

}
