<?php

namespace Hametuha\WpApi;

use Hametuha\WpApi\Pattern\IdeaApiPattern;

/**
 * 他人のアイデアを見るためのREST API
 */
class Ideas extends IdeaApiPattern {

	protected function get_route() {
		return 'idea/(?<post_id>\d+)/?';
	}

	protected function get_arguments( $method ) {
		$args = [
			'post_id' => [
				'type'              => 'integer',
				'validate_callback' => [ $this, 'is_idea' ],
				'required'          => true,
			],
		];
		if ( 'PUT' === $method ) {
			$args['user_id'] = [
				'type'              => 'integer',
				'validate_callback' => function ( $user_id ) {
					// ユーザーが存在するか
					if ( ! is_numeric( $user_id ) || ! get_user_by( 'id', $user_id ) ) {
						return new \WP_Error( 'invalid_user', '該当するユーザーは存在しません。', [ 'status' => 400 ] );
					}
					return true;
				},
				'required'          => true,
			];
		}
		return $args;
	}

	/**
	 * アイデアを取得する
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function handle_get( $request ) {
		$post_id = $request->get_param( 'post_id' );
		$post    = get_post( $post_id );

		if ( ! $post ) {
			return new \WP_Error( 'not_found', 'アイデアが見つかりません。', [ 'status' => 404 ] );
		}

		// 非公開の場合は作者のみアクセス可能
		if ( 'private' === $post->post_status && get_current_user_id() !== intval( $post->post_author ) ) {
			return new \WP_Error( 'forbidden', 'このアイデアにアクセスする権限がありません。', [ 'status' => 403 ] );
		}

		// タグを取得
		$tags  = wp_get_post_tags( $post_id, [ 'fields' => 'ids' ] );
		$genre = ! empty( $tags ) ? $tags[0] : '';

		return new \WP_REST_Response( [
			'success'   => true,
			'post_id'   => $post->ID,
			'title'     => $post->post_title,
			'content'   => $post->post_content,
			'status'    => $post->post_status,
			'genre'     => $genre,
			'author_id' => $post->post_author,
			'can_edit'  => current_user_can( 'edit_post', $post->ID ),
		] );
	}

	/**
	 * アイデアをストックする
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	protected function handle_post( $request ) {
		$post = get_post( $request['post_id'] );
		if ( $this->ideas->is_stocked( get_current_user_id(), $post->ID, true ) ) {
			$this->ideas->restock( get_current_user_id(), $post->ID );
		} else {
			if ( ! $this->ideas->stock( get_current_user_id(), $post->ID ) ) {
				return new \WP_Error( 'server_error', '保存に失敗しました。あとでやり直してください。', [ 'status' => 500 ] );
			}
			$current_user = get_userdata( get_current_user_id() );
			if ( get_current_user_id() != $post->post_author ) {
				$notified = $this->notifications->add_idea_stocked( $post->post_author, $post->ID,
					sprintf( '%sさんがあなたのアイデア「%s」をストックしました。', $current_user->display_name, $post->post_title ),
				get_current_user_id() );
			}
		}

		return new \WP_REST_Response( [
			'message' => sprintf( 'アイデア: %s をストックしました。', $post->post_title ),
		] );
	}

	/**
	 * アイデアを推薦する
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	protected function handle_put( $request ) {
		$post = get_post( $request['post_id'] );
		if ( ! ( $user = get_userdata( $request['user_id'] ) ) || ! $user->has_cap( 'edit_posts' ) ) {
			return new \WP_Error( 'not_found', '指定されたユーザーは存在しません。', [ 'status' => 404 ] );
		}
		if ( $post->post_author == $user->ID ) {
			return new \WP_Error( 'duplicated', sprintf( '%sさんはこのアイデアの作者です。', $post->post_title ), [ 'status' => 500 ] );
		}
		if ( $this->ideas->is_stocked( $user->ID, $post->ID, true ) ) {
			return new \WP_Error( 'duplicated', sprintf( '%sさんはこのアイデアを検討したことがあるようです。', $post->post_title ), [ 'status' => 500 ] );
		}
		if ( ! $this->ideas->recommend( get_current_user_id(), $user->ID, $post->ID ) ) {
			return new \WP_Error( 'server_error', '保存に失敗しました。あとでやり直してください。', [ 'status' => 500 ] );
		}
		$current_user = get_userdata( get_current_user_id() );
		$this->notifications->add_notification( 'idea_recommended', $user->ID, $post->ID,
			sprintf( '%sさんから「%s」というアイデアが勧められています。', $current_user->display_name, $post->post_title ),
			get_current_user_id()
		);

		return new \WP_REST_Response( [
			'success' => true,
			'message' => 'アイデアをおすすめしました。',
		] );
	}

	/**
	 * アイデアをストックから出す
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	protected function handle_delete( $request ) {
		$post = get_post( $request['post_id'] );
		if ( ! $this->ideas->is_stocked( get_current_user_id(), $post->ID ) ) {
			return new \WP_Error( 'duplicated', 'このアイデアは削除済みか、ストックしていません。', [ 'status' => 404 ] );
		}
		if ( ! $this->ideas->trash( get_current_user_id(), $post->ID ) ) {
			return new \WP_Error( 'server_error', '保存に失敗しました。あとでやり直してください。', [ 'status' => 500 ] );
		}

		return new \WP_REST_Response( [
			'message' => sprintf( 'アイデア: %s をストックから除きました。', $post->post_title ),
		] );
	}
}
