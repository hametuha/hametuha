<?php

namespace Hametuha\WpApi;


use Hametuha\Model\Review;
use WPametu\API\Rest\WpApi;

/**
 * ユーザーからのレビュータグを操作する
 *
 * @feature-group feedback
 * @property-read Review $review
 */
class FeedbackReview extends WpApi {

	protected $models = [
		'review' => Review::class,
	];

	protected function get_route() {
		return 'feedback/review/(?P<post_id>\d+)/?';
	}

	protected function get_arguments( $method ) {
		$args = [
			'post_id' => [
				'required'          => true,
				'type'              => 'integer',
				'validate_callback' => function ( $var ) {
					$post = get_post( $var );
					// 自分の投稿はレビューできない
					return $post && ( get_current_user_id() !== (int) $post->post_author );
				},
			],
		];
		if ( 'POST' === $method ) {
			foreach ( $this->review->feedback_tags as $key => $labels ) {
				$args[ $key ] = [
					'required'          => false,
					'type'              => 'string',
					'default'           => '',
					'validate_callback' => function ( $var ) use ( $labels ) {
						return empty( $var ) || in_array( $var, $labels, true );
					},
				];
			}
		}
		return $args;
	}

	/**
	 * ユーザーがつけたレビュータグを返す
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response
	 */
	public function handle_get( $request ) {
		$post_id = $request->get_param( 'post_id' );
		$tags    = $this->review->user_voted_tags( get_current_user_id(), $post_id );

		// タグ名の配列に変換
		$tag_names = [];
		foreach ( $tags as $tag ) {
			$tag_names[] = $tag->name;
		}

		return new \WP_REST_Response( [
			'tags' => $tag_names,
		] );
	}

	/**
	 * レビューを更新する
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function handle_post( $request ) {
		$post = get_post( $request->get_param( 'post_id' ) );
		// 登録ユーザーなら、既存のものがあれば削除
		$this->review->clear_user_review( get_current_user_id(), $post->ID );
		// データを挿入
		$reviewed_terms = [];
		foreach ( $this->review->feedback_tags as $key => $terms ) {
			$value = $request->get_param( $key );
			if ( empty( $value ) ) {
				continue;
			}
			$term = get_term_by( 'name', $value, $this->review->taxonomy );
			if ( ! $term ) {
				return new \WP_Error( 'invalid_review', sprintf( '指定したレビュータグ「%s」はありません', $value ), [
					'status' => 400,
				] );
			} elseif ( is_wp_error( $term ) ) {
				return $term;
			}
			$this->review->add_review( get_current_user_id(), $post->ID, $term->term_taxonomy_id );
			$reviewed_terms[] = $term;
		}
		// レビューの総数を更新
		$this->review->update_review_count( $post->ID );
		// レビュータグの集計データをキャッシュ
		$this->review->update_post_review_tags( $post->ID );
		/**
		 * hametuha_post_reviewed
		 *
		 * Fired when review is saved
		 *
		 * @param \WP_Post $post
		 * @param int $user_id
		 * @param array $reviewed_terms
		 * @param int $rank
		 */
		do_action( 'hametuha_post_reviewed', $post, get_current_user_id(), $reviewed_terms );
		return new \WP_REST_Response( [
			'success' => true,
			'message' => 'レビューを保存しました。ありがとうございました。',
		] );
	}

	public function permission_callback( $request ) {
		return current_user_can( 'read' );
	}
}
