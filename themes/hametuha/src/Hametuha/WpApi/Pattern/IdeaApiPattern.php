<?php

namespace Hametuha\WpApi\Pattern;


use Hametuha\Model\Ideas;
use \Hametuha\Model\Notifications;
use WPametu\API\Rest\WpApi;


/**
 * Idea API pattern
 *
 * @property-read Ideas $ideas アイデアのモデルインスタンス。
 * @property-read Notifications $notifications 通知のモデルインスタンス。
 */
abstract class IdeaApiPattern extends WpApi {


	protected $models = [
		'ideas'         => Ideas::class,
		'notifications' => Notifications::class,
	];

	/**
	 * Detect if this is idea object.
	 *
	 * @param int $post_id
	 *
	 * @return bool|\WP_Error
	 */
	public function is_idea( $post_id ) {
		if ( ! is_numeric( $post_id ) || ! ( $post = get_post( $post_id ) ) || 'ideas' !== $post->post_type ) {
			return new \WP_Error( 'invalid_post_type', '該当するアイデアは存在しません。', [ 'status' => 403 ] );
		}

		return true;
	}


	public function permission_callback( $request ) {
		return current_user_can( 'read' );
	}
}
