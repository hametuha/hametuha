<?php

namespace Hametuha\WpApi\Pattern;


use Hametuha\Model\Ideas;
use Hametuha\Model\Notifications;
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

	/**
	 * タグがジャンルに属するものかどうか判定する
	 *
	 * @param int $term_id タグID
	 *
	 * @return bool
	 */
	public function is_valid_tag( $term_id ) {
		$term = get_tag( $term_id );

		return $term && ! is_wp_error( $term ) && ( 'サブジャンル' === get_term_meta( $term->term_id, 'genre', true ) );
	}

	public function permission_callback( $request ) {
		return current_user_can( 'read' );
	}
}
