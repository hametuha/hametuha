<?php

namespace Hametuha\Model;


use WPametu\DB\Model;

/**
 * 通知のモデルクラス
 *
 * @package Hametuha\Model
 * @method bool add_comment(int $recipient, int $object_id, string $message, string $avatar)
 * @method bool add_review(int $recipient, int $object_id, string $message, string $avatar)
 * @method bool add_hot(int $recipient, int $object_id, string $message, string $avatar)
 * @method bool add_general(int $recipient, int $object_id, string $message, string $avatar)
 */
class Notifications extends Model
{

    protected $name = 'notifications';


	protected $default_placeholder = [
		'recipient_id' => '%d',
	    'type' => '%s',
	    'object_id' => '%d',
	    'message' => '%s',
	    'avatar' => '%s',
	    'created' => '%s',
	];

	const TYPE_COMMENT = 'comment';

	const TYPE_REVIEW = 'review';

	const TYPE_HOT    = 'hot';

	const TYPE_GENERAL = 'general';

	const USER_KEY = 'last_notification_checked';

    /**
     * 通知確認時間を更新
     *
     * @param int $user_id
     * @return bool
     */
	public function update_login($user_id){
		$time = current_time('timestamp');
        update_user_meta($user_id, static::USER_KEY, $time);
		return $time;
    }

	/**
	 * 最後にチェックした時間を表示
	 *
	 * @param int $user_id
	 *
	 * @return int
	 */
	public function get_last_checked($user_id){
		return (int) get_user_meta($user_id, static::USER_KEY, true);
	}

	/**
	 * Return URL
	 *
	 * @param string $type
	 * @param int $object_id
	 *
	 * @return bool|string
	 */
	public function build_url($type, $object_id){
		switch( $type ){
			case static::TYPE_COMMENT:
				$url = get_comment_link($object_id);
				break;
			default:
				$url = get_permalink($object_id);
				break;
		}
		return $url;
	}

	/**
	 * Get recent results
	 *
	 * @param int $user_id
	 *
	 * @return array|mixed|null
	 */
	public function get_recent($user_id){
		$notifications = wp_cache_get($user_id, 'hametuha_notifications');
		if( false === $notifications ){
			$limit = 5;
			$this->where_in('recipient_id', [0, $user_id], '%d');
			$notifications = $this->limit($limit)->order_by("created", 'DESC')->result();
			if( $notifications ){
				wp_cache_set($user_id, $notifications, 'hametuha_notifications', 1800);
			}
		}
		return $notifications;
	}



	/**
	 * 順番に通知を取得する
	 *
	 * @param int|array $user_ids
	 * @param string $type
	 * @param int $paged
	 *
	 * @return array|mixed|null
	 */
	public function get_notifications($user_ids = [], $type = '', $paged = 1){
		if( !$user_ids ){
			$user_ids = [get_current_user_id()];
		}
		if( count($user_ids) > 1 ){
			$this->where_in('recipient_id', $user_ids, '%d');
		}else{
			$this->where("recipient_id = %d", $user_ids[0]);
		}
		if( $type ){
			$this->where("type = %s", $type);
		}
		$result = $this->calc(true)->limit(10, (max($paged, 1) - 1) * 10)->order_by("created", 'DESC')->result();
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
	public function add_notification($type, $recipient, $object_id, $message, $avatar){
		if( $recipient ){
			wp_cache_delete($recipient, 'hametuha_notifications');
		}
		return (bool) $this->insert([
			'recipient_id' => $recipient,
		    'type' => $type,
		    'object_id' => $object_id,
		    'message' => $message,
		    'avatar' => $avatar,
		    'created' => current_time('mysql'),
		]);
	}

	/**
	 * Magic method
	 *
	 * @param string $name
	 * @param array $arguments
	 *
	 * @return mixed
	 */
	public function __call($name, array $arguments = []){
		if( preg_match('/^add_([a-z]+)$/', $name, $match) ){
			switch( $match[1] ){
				case static::TYPE_COMMENT:
				case static::TYPE_HOT:
				case static::TYPE_REVIEW:
				case static::TYPE_GENERAL:
					array_unshift($arguments, $match[1]);
					return call_user_func_array([$this, 'add_notification'], $arguments);
					break;
				default:
					// Do nothing
					break;
			}
		}
	}
}
