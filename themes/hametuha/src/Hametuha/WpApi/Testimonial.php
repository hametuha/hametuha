<?php

namespace Hametuha\WpApi;


use Hametuha\Model\Series;
use WPametu\API\Rest\WpApi;

/**
 * 推薦コメントの編集API
 *
 * @feature-group series
 * @property-read Series $series
 */
class Testimonial extends WpApi {

	protected $models = [
		'series' => Series::class,
	];

	protected function get_route() {
		return 'testimonials/(?P<id>\d+)/?';
	}

	protected function get_arguments( $method ) {
		$args = [
			'id' => [
				'type'              => 'integer',
				'required'          => true,
				'validate_callback' => function ( $id, \WP_REST_Request $request ) {
					if ( 'POST' === $request->get_method() ) {
						// POSTのときは投稿
						$post = get_post( $id );
						return ( $post && 'series' === $post->post_type );
					} else {
						// それ以外はコメント
						$comment = get_comment( $id );
						return ( is_a( $comment, 'WP_Comment' ) && in_array( $comment->comment_type, [ 'comment', 'review' ], true ) );
					}
				},
			],
		];
		if ( in_array( $method, [ 'POST', 'PUT' ] ) ) {
			$args = array_merge( $args, [
				'testimonial-source' => [
					'default' => '',
					'type'    => 'string',
					'validate_callback' => [ $this, 'only_empty_with_twitter_url' ],
				],
				'testimonial-text' => [
					'default' => '',
					'type'    => 'string',
					'validate_callback' => [ $this, 'only_empty_with_twitter_url' ],
				],
				'testimonial-rank' => [
					'default'           => 0,
					'type'              => 'integer',
					'validate_callback' => function( $rank ) {
						return in_array( $rank, range( 0, 5 ) );
					},
				],
				'testimonial-url' => [
					'default' => '',
					'type'    => 'string',
					'validate_callback' => function( $url ) {
						return empty( $url ) || wp_http_validate_url( $url );
					},
				],
			] );
		}
		if ( 'PUT' === $method ) {
			$args = array_merge( $args, [
				'testimonial-display' => [
					'required'          => true,
					'type'              => 'integer',
					'validate_callback' => function ( $status ) {
						return in_array( (int) $status, [ 0, 1 ], true );
					},
				],
				'testimonial-priority' => [
					'default'           => 0,
					'type'              => 'integer',
					'validate_callback' => function ( $priority ) {
						return is_numeric( $priority ) && $priority >= 0;
					},
				],
				'testimonial-excerpt' => [
					'default' => '',
					'type'    => 'string',
				],
			] );
		}
		return $args;
	}

	/**
	 * レビューの新規追加
	 *
	 * @param \WP_REST_Request $request
	 * @param $request
	 */
	protected function handle_post( $request ) {
		$post_id = $request->get_param( 'id' );
		$comment_id = wp_insert_comment( [
			'comment_author'       => $request->get_param( 'testimonial-source' ),
			'user_id'              => get_current_user_id(),
			'comment_content'      => $request->get_param( 'testimonial-text' ),
			'comment_post_ID'      => $post_id,
			'comment_type'         => 'review',
			'comment_author_url'   => $request->get_param( 'testimonial-url' ),
			'comment_approved'     => current_user_can( 'edit_post', $post_id ) ? '1' : '0',
			'comment_author_email' => get_userdata( get_current_user_id() )->user_email,
			'comment_agent'        => $_SERVER['HTTP_USER_AGENT'],
			'comment_author_IP'    => $_SERVER['REMOTE_ADDR'],
		] );
		update_comment_meta( $comment_id, 'testimonial_rank', $request->get_param( 'testimonial-rank' ) );
		return new \WP_REST_Response( [
			'success' => true,
			'message' => current_user_can( 'edit_post', $post_id )
				? 'レビューが登録されました。'
				: 'ありがとうございました。作者によって承認された場合は公開されます。',
		] );
	}

	/**
	 * 推薦コメントを削除する
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_Error|\WP_REST_Response
	 */
	protected function handle_delete( $request ) {
		$comment_id = $request->get_param( 'id' );
		$comment    = get_comment( $comment_id );

		$result = wp_delete_comment( $comment->comment_ID, true );
		if ( ! $result ) {
			return new \WP_Error( 'testimonial-error', __( 'レビューの削除に失敗しました。', 'hametuha' ), [ 'status' => 500 ] );
		}

		return new \WP_REST_Response( [
			'message' => __( 'レビューを削除しました。', 'hametuha' ),
			'success' => true,
		] );
	}

	/**
	 * レビューを更新する
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function handle_put( $request ) {
		$comment_id = $request->get_param( 'id' );
		$comment    = get_comment( $comment_id );

		// twitterかどうか、親コメント（series直下）かをチェック
		$is_twitter = $this->series->is_service( $comment->comment_author_url, 'twitter' );
		$is_parent  = 'series' === get_post_type( $comment->comment_post_ID );

		// 優先順位を更新
		$priority = $request->get_param( 'testimonial-priority' );
		update_comment_meta( $comment_id, 'testimonial_order', $priority );

		// 公開状態
		$status = (bool) $request->get_param( 'testimonial-display' );

		if ( 'review' === $comment->comment_type ) {
			// レビュータイプ（series直下のコメント）
			$comment_arr = [
				'comment_ID'         => $comment_id,
				'comment_approved'   => (int) $status,
				'comment_author_url' => $request->get_param( 'testimonial-url' ),
			];

			// Twitter以外の場合のみ著者名と本文を更新
			if ( ! $is_twitter ) {
				$comment_arr['comment_author']  = $request->get_param( 'testimonial-source' );
				$comment_arr['comment_content'] = $request->get_param( 'testimonial-text' );

				// 親かつtwitter以外の場合は評価を更新
				if ( $is_parent ) {
					update_comment_meta( $comment_id, 'testimonial_rank', (int) $request->get_param( 'testimonial-rank' ) );
				}
			}

			$result = wp_update_comment( $comment_arr );
			if ( false === $result ) {
				return new \WP_Error( 'testimonial-error', __( 'コメントの更新に失敗しました。', 'hametuha' ), [ 'status' => 500 ] );
			}
		} else {
			// 通常コメント（子投稿へのコメント）
			update_comment_meta( $comment_id, 'as_testimonial', $status );

			// 抜粋の更新
			$excerpt = $request->get_param( 'testimonial-excerpt' );
			if ( ! empty( $excerpt ) ) {
				// 抜粋がコメント本文に含まれているかチェック
				foreach ( explode( "\n", $excerpt ) as $line ) {
					$line = trim( $line );
					if ( empty( $line ) ) {
						continue;
					}
					if ( false === strpos( $comment->comment_content, $line ) ) {
						return new \WP_Error( 'testimonial-error', __( 'コメントに含まれる文字列以外は登録できません。', 'hametuha' ), [ 'status' => 400 ] );
					}
				}
			}
			update_comment_meta( $comment_id, 'comment_excerpt', $excerpt );
		}

		return new \WP_REST_Response( [
			'success' => true,
			'message' => __( 'レビューを更新しました。', 'hametuha' ),
		] );
	}

	public function permission_callback( $request ) {
		if ( 'POST' === $request->get_method() ) {
			return current_user_can( 'read' );
		} else {
			return current_user_can( 'edit_comment', $request->get_param( 'id' ) );
		}
	}

	/**
	 * URLがtwitterの場合だけtrue
	 *
	 * @param string $text
	 * @param \WP_REST_Request $request
	 *
	 * @return bool
	 */
	public function only_empty_with_twitter_url( $text, $request ) {
		if ( empty( $text ) && ! $this->series->is_service( $request->get_param( 'testimonial-url' ), 'twitter' ) ) {
			return false;
		}
		return true;
	}
}
