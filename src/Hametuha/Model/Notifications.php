<?php

namespace Hametuha\Model;


use WPametu\DB\Model;

/**
 * 通知のモデルクラス
 *
 * @package Hametuha\Model
 * @method bool add_comment( $recipient, $object_id, $message, $avatar )
 * @method bool add_idea_stocked( $recipient, $object_id, $message, $avatar )
 * @method bool add_idea_written( $recipient, $object_id, $message, $avatar )
 * @method bool add_review( $recipient, $object_id, $message, $avatar )
 * @method bool add_hot( $recipient, $object_id, $message, $avatar )
 * @method bool add_follow( $recipient, $object_id, $message, $avatar )
 * @method bool add_general( $recipient, $object_id, $message, $avatar )
 */
class Notifications extends Model {

	protected $name = 'notifications';

	const PER_PAGE = 10;

	protected $default_placeholder = [
		'recipient_id' => '%d',
		'type'         => '%s',
		'object_id'    => '%d',
		'message'      => '%s',
		'avatar'       => '%s',
		'created'      => '%s',
	];

	const TYPE_COMMENT = 'comment';

	const TYPE_REVIEW = 'review';

	const TYPE_HOT = 'hot';

	const TYPE_GENERAL = 'general';

	const TYPE_FOLLOW = 'follow';

	const TYPE_IDEA_STOCKED = 'idea_stocked';

	const TYPE_IDEA_WRITTEN = 'idea_written';

	const USER_KEY = 'last_notification_checked';

	/**
	 * 通知確認時間を更新
	 *
	 * @param int $user_id
	 *
	 * @return bool
	 */
	public function update_login( $user_id ) {
		$time = current_time( 'timestamp' );
		update_user_meta( $user_id, static::USER_KEY, $time );

		return $time;
	}

	/**
	 * 最後にチェックした時間を表示
	 *
	 * @param int $user_id
	 *
	 * @return int
	 */
	public function get_last_checked( $user_id ) {
		return (int) get_user_meta( $user_id, static::USER_KEY, true );
	}

	/**
	 * Return URL
	 *
	 * @param string $type
	 * @param int $object_id
	 *
	 * @return bool|string
	 */
	public function build_url( $type, $object_id ) {
		switch ( $type ) {
			case static::TYPE_COMMENT:
				$url = get_comment_link( $object_id );
				break;
			case static::TYPE_FOLLOW:
				$url = home_url( '/doujin/follower/', 'https' );
				break;
			case 'idea_recommended':
				$url = home_url( '/my/ideas/', 'https' );
				break;
			default:
				$url = get_permalink( $object_id );
				break;
		}

		return $url;
	}

	/**
	 * Get recent results
	 *
	 * @param int  $user_id
	 * @param bool $include_general
	 *
	 * @return array|mixed|null
	 */
	public function get_recent( $user_id, $include_general = true ) {
		$notifications = wp_cache_get( $user_id, 'hametuha_notifications' );
		$limit         = 5;
		if ( false === $notifications ) {
			$notifications = $this->where( 'recipient_id = %d', $user_id )
								  ->limit( $limit )->order_by( 'created', 'desc' )->result();
			if ( $notifications ) {
				wp_cache_set( $user_id, $notifications, 'hametuha_notifications', 1800 );
			}
		}
		if ( $include_general ) {
			$general = wp_cache_get( 0, 'hametuha_notifications' );
			if ( false === $general ) {
				$general = $this->where( 'recipient_id = %d', 0 )
								->limit( $limit )->order_by( 'created', 'desc' )->result();
				if ( $general ) {
					wp_cache_set( 0, $general, 'hametuha_notifications', 1800 );
				}
			}
			$notifications = array_merge( $general, $notifications );
			usort( $notifications, function ( $a, $b ) {
				$a_time = strtotime( $a->created );
				$b_time = strtotime( $b->created );
				if ( $a_time === $b_time ) {
					return 0;
				} else {
					return $a_time < $b_time ? 1 : -1;
				}
			} );
			$notifications = array_slice( $notifications, 0, 5 );
		}

		return $notifications;
	}


	/**
	 * 順番に通知を取得する
	 *
	 * @param int|array $user_ids
	 * @param string $type
	 * @param int $paged
	 * @param int $per_page
	 *
	 * @return array|mixed|null
	 */
	public function get_notifications( $user_ids = [], $type = '', $paged = 1, $per_page = 0 ) {
		if ( ! $per_page ) {
			$per_page = self::PER_PAGE;
		}
		if ( ! $user_ids ) {
			$user_ids = [ get_current_user_id() ];
		}
		if ( count( $user_ids ) > 1 ) {
			$this->where_in( 'recipient_id', $user_ids, '%d' );
		} else {
			$this->where( 'recipient_id = %d', $user_ids[0] );
		}
		if ( $type ) {
			$this->where( 'type = %s', $type );
		}
		$result = $this->calc( true )->limit( $per_page, ( max( $paged, 1 ) - 1 ) )->order_by( 'created', 'DESC' )->result();

		return $result;
	}

	/**
	 * 通知を保存
	 *
	 * @param string $type
	 * @param int $recipient
	 * @param int $object_id
	 * @param string $message
	 *
	 * @return bool
	 */
	public function add_notification( $type, $recipient, $object_id, $message, $avatar ) {
		wp_cache_delete( $recipient, 'hametuha_notifications' );
		return (bool) $this->insert( [
			'recipient_id' => $recipient,
			'type'         => $type,
			'object_id'    => $object_id,
			'message'      => $message,
			'avatar'       => $avatar,
			'created'      => current_time( 'mysql' ),
		] );
	}

	/**
	 * Magic method
	 *
	 * @param string $name
	 * @param array $arguments
	 *
	 * @return mixed
	 */
	public function __call( $name, array $arguments = [] ) {
		if ( preg_match( '/^add_([a-z_]+)$/', $name, $match ) ) {
			switch ( $match[1] ) {
				case static::TYPE_COMMENT:
				case static::TYPE_HOT:
				case static::TYPE_REVIEW:
				case static::TYPE_GENERAL:
				case static::TYPE_FOLLOW:
				case static::TYPE_IDEA_STOCKED:
				case static::TYPE_IDEA_WRITTEN:
					array_unshift( $arguments, $match[1] );
					return call_user_func_array( [ $this, 'add_notification' ], $arguments );
					break;
				default:
					// Do nothing.
					break;
			}
		}
	}
}
