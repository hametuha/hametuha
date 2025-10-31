<?php

namespace Hametuha\WpApi;


use WPametu\API\Rest\WpApi;
use Hametuha\Model\Rating;

/**
 * レーティングのためのREST APIエンドポイント
 *
 * @property-read Rating $rating
 */
class FeedbackRating extends WpApi {

	protected $models = [
		'rating' => Rating::class,
	];

	protected function get_route() {
		return 'feedback/rating/(?P<post_id>\d+)/?';
	}

	protected function get_arguments( $method ) {
		$args = [
			'post_id' => [
				'required'          => true,
				'type'              => 'integer',
				'validate_callback' => function( $var ) {
					$post = get_post( $var );
					// 自分の投稿はレビューできない
					return $post && ( get_current_user_id() !== (int) $post->post_author );
				},
			],
		];
		if ( 'POST' === $method ) {
			$args['rating'] =[
				'required'          => true,
				'type'              => 'integer',
				'validate_callback' => function( $var ) {
					return in_array( $var, range( 0, 5 ) );
				},
			];
		}
		return $args;
	}

	/**
	 * ユーザーがつけたレーティングを返す
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response
	 */
	public function handle_get( $request ) {
		$current_rating = $this->rating->get_users_rating( $request->get_param( 'post_id' ), get_current_user_id() ) ?: 0;
		return new \WP_REST_Response( [
			'rating' => $current_rating,
		] );
	}

	public function handle_post( $request ) {
		$post_id = $request->get_param( 'post_id' );
		$rating  = $request->get_param( 'rating' );
		if ( 0 === $rating ) {
			// 0の場合はレコードを削除する。
			$result = $this->rating->delete_rating( get_current_user_id(), $post_id );
			if ( ! $result ) {
				return new \WP_Error( 'rating_error', __( 'レーティングの更新に失敗しました。', 'hametuha' ), [
					'status' => 500,
				] );
			}
		} else {
			// 1-5は新規保存
			$result = $this->rating->update_rating( $rating, get_current_user_id(), $post_id );
		}
		// データを保存
		$this->rating->update_post_average( $post_id );
		// レスポンスを返す
		return new \WP_REST_Response( [
			'rating'  => $rating,
			'message' => __( 'レーティングを更新しました。', 'hametuha' ),
		] );
	}

	public function permission_callback( $request ) {
		return current_user_can( 'read' );
	}
}
