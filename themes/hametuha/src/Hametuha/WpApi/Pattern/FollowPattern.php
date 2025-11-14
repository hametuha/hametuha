<?php

namespace Hametuha\WpApi\Pattern;


use Hametuha\Model\Follower;
use Hametuha\Model\Notifications;
use WPametu\API\Rest\WpApi;

/**
 * フォロワーを扱うエンドポイント
 *
 * @property-read Follower $follower
 * @property-read Notifications $notifications
 */
abstract class FollowPattern extends WpApi {

	/**
	 * @var array
	 */
	protected $models = [
		'follower'      => Follower::class,
		'notifications' => Notifications::class,
	];

	/**
	 * 指定されたものがmeかユーザーID
	 *
	 * @param string $var
	 * @return bool
	 */
	public function id_or_me( $var ) {
		if ( 'me' === $var ) {
			return true;
		}
		return $this->is_user_id( $var );
	}

	/**
	 * 指定されたユーザーIDが有効か
	 *
	 * @param int $var ユーザーID
	 * @return bool
	 */
	public function is_user_id( $var ) {
		if ( ! is_numeric( $var ) ) {
			return false;
		}
		return (bool) get_userdata( $var );
		if ( ! $user ) {
			// 見つからないのでエラー
			return false;
		}
		return $user;
	}

	public function permission_callback( $request ) {
		return current_user_can( 'read' );
	}

	/**
	 * Sanitize User
	 *
	 * @param \stdClass|\WP_User $user User object.
	 * @param bool $additional Default true.
	 *
	 * @return \stdClass|\WP_User
	 */
	protected function sanitize_user( $user, $additional = true ) {
		// Additional information.
		if ( $additional ) {
			$user->isAuthor = user_can( $user->ID, 'edit_posts' );
			$user->isEditor = user_can( $user->ID, 'edit_others_posts' );
			$user->avatar   = preg_replace( '#^.*src=[\'"]([^\'"]+)[\'"].*$#', '$1', get_avatar( $user->ID, 96 ) );
		}
		// Remove credentials.
		unset( $user->user_email );
		unset( $user->user_pass );
		unset( $user->user_activation_key );

		return $user;
	}
}
