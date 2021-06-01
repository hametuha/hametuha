<?php

namespace Hametuha\Hooks;


use Hametuha\AbstractPatterns\AbstractRecorders;

/**
 * Record user action
 *
 * @package hametuha
 */
class RecordUserAction extends AbstractRecorders {

	/**
	 * Executed inside constructor.
	 *
	 * @return void
	 */
	protected function init() {
		// User created.
		add_action( 'register_new_user', [ $this, 'user_registered' ] );
		// Account deleted.
		add_action( 'delete_user', [ $this, 'user_deleted' ], 10, 2 );
		// Login
		add_action( 'wp_login', [ $this, 'user_logged_in' ], 10, 2 );
		// Become author.
		add_action( 'hametuha_user_became_author', [ $this, 'became_author' ] );
		// Login failed.
		add_action( 'wp_login_failed', [ $this, 'login_failed' ] );
	}

	/**
	 * Save account event.
	 *
	 * @param string $action
	 * @param int $user_id
	 */
	private function save_event( $action, $user_id ) {
		$this->save_user_event( $action, $user_id, 'account', $user_id );
	}

	/**
	 * Save if user logged in.
	 *
	 * @param string   $login_name
	 * @param \WP_User $user
	 */
	public function user_logged_in( $login_name, $user ) {
		$this->save_event( 'login', $user->ID );
	}

	/**
	 * New user is registered.
	 *
	 * @param int $user_id
	 */
	public function user_registered( $user_id ) {
		$this->save_event( 'register', $user_id );
	}

	/**
	 * User is deleted.
	 *
	 * @param int  $user_id
	 * @param null|int $reassign
	 */
	public function user_deleted( $user_id, $reassign ) {
		$this->save_event( 'delete', $user_id );
	}

	/**
	 * Became author.
	 *
	 * @param int $user_id
	 */
	public function became_author( $user_id ) {
		$this->save_event( 'author', $user_id );
	}

	/**
	 * Save login failed.
	 *
	 * @param string $user_name
	 */
	public function login_failed( $user_name ) {
		$this->analytics->measurement->event(
			[
				'ea' => 'failed',
				'ec' => 'account',
				'el' => $user_name,
			]
		);
	}
}
