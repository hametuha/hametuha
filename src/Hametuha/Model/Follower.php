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
			                   'target_id'    => $user_id,
			                   'status'       => 0,
		                   ] )->get_var();
	}

}
