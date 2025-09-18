<?php

namespace Hametuha\WpApi;


use Hametuha\ThePost\Announcement;
use WPametu\API\Rest\WpApi;

/**
 * イベント参加をコントロールするREST API
 *
 */
class EventParticipants extends WpApi {

	protected function get_route() {
		return 'participants/(?P<post_id>\d+)/?';
	}

	protected function get_arguments( $method ) {
		return [
			'post_id' => [
				'required'          => true,
				'type'              => 'integer',
				'description'       => 'Event Post ID to handle.',
				'validate_callback' => function( $post_id ) {
					if ( ! is_numeric( $post_id ) ) {
						return false;
					}
					$post = get_post( $post_id );
					if ( ! $post || ! in_array( $post->post_type, [ 'announcement', 'news' ], true ) ) {
						return false;
					}
					$event = new Announcement( $post );
					return $event->can_participate();
				},
			],
			'text'    => [
				'type'    => 'string',
				'default' => '',
			],
		];
	}

	/**
	 * 現在のイベント参加状況を取得
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	protected function handle_get( $request ) {
		$post  = get_post( $request['post_id'] );
		$event = new Announcement( $post );
		$user_id = get_current_user_id();

		return new \WP_REST_Response( [
			'participants'     => $event->get_participants(),
			'count'           => $event->participating_count(),
			'limit'           => $event->participating_limit( false ),
			'in_list'         => $event->in_list( $user_id ),
			'my_comment'      => $event->guest_comment( $user_id ),
			'can_participate' => $event->can_participate(),
			'event_id'        => $post->ID,
		] );
	}

	/**
	 * イベントの参加状況を切り替える
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	protected function handle_post( $request ) {
		$post  = get_post( $request['post_id'] );
		$event = new Announcement( $post );
		// todo: 募集時期のチェック

		// 定員チェック
		$limit = $event->participating_limit( false );
		if ( $limit && $limit <= $event->participating_count() ) {
			return new \WP_Error( 'event_participation_failure', __( 'すでに定員に達しています。', 'hametuha' ), [ 'status' => 400 ] );
		}
		// すでに参加中かどうかチェック
		if ( $event->in_list() ) {
			return new \WP_Error( 'event_participation_failure', __( 'すでに参加中です', 'hametuha' ), [ 'status' => 400 ] );
		}
		// チケットを取得
		$ticket_id = $event->get_ticket_id( get_current_user_id() );
		$updated   = true;
		if ( ! $ticket_id ) {
			$updated   = false;
			$ticket_id = wp_insert_comment( [
				'comment_type'     => 'participant',
				'comment_post_ID'  => $post->ID,
				'comment_content'  => $request->get_param('text'),
				'comment_approved' => 1,
				'user_id'          => get_current_user_id(),
			] );
			if ( ! $ticket_id ) {
				return new \WP_Error( 'event_participation_failure', __( '登録に失敗しました。時間をおいてお試しください。', 'hametuha' ), [ 'status' => 500 ] );
			}
		}
		// 更新する
		update_comment_meta( $ticket_id, '_participating', 1 );
		if ( get_current_user_id() != $post->post_author ) {
			// 主催者に連絡
			$organizer = get_userdata( $post->post_author );
			do_action( 'hametuha_notification', 'participant', "参加状況: {$post->post_title}", $organizer->user_email, [
				'post'        => $post,
				'status'      => $request['status'],
				'organizer'   => $organizer,
				'participant' => get_userdata( get_current_user_id() ),
				'update'      => $updated,
				'message'     => $request['text'],
			] );
		}
		return new \WP_REST_Response( $event->get_user_object( $ticket_id ) );
	}

	/**
	 * 参加コメントのみを更新
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	protected function handle_put( $request ) {
		$post  = get_post( $request['post_id'] );
		$event = new Announcement( $post );

		// 参加中かどうかチェック
		if ( ! $event->in_list( get_current_user_id() ) ) {
			return new \WP_Error( 'event_participation_failure', __( 'このイベントには参加していません。', 'hametuha' ), [ 'status' => 400 ] );
		}

		// チケットを取得
		$ticket_id = $event->get_ticket_id( get_current_user_id() );
		if ( ! $ticket_id ) {
			return new \WP_Error( 'event_participation_failure', __( 'チケットが見つかりません。', 'hametuha' ), [ 'status' => 404 ] );
		}

		// コメントを更新
		wp_update_comment( [
			'comment_ID'      => $ticket_id,
			'comment_content' => $request->get_param( 'text' ),
		] );

		return new \WP_REST_Response( [
			'result'  => true,
			'message' => __( 'コメントを更新しました。', 'hametuha' ),
			'text'    => $request->get_param( 'text' ),
		] );
	}

	/**
	 * イベントの参加を取り消す
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	protected function handle_delete( $request ) {
		$post  = get_post( $request['post_id'] );
		$event = new Announcement( $post );
		// すでに参加中かどうかチェック
		if ( ! $event->in_list() ) {
			return new \WP_Error( 'event_participation_failure', __( 'このイベントには参加していません。', 'hametuha' ), [ 'status' => 400 ] );
		}
		// チケットを取得
		$ticket_id = $event->get_ticket_id( get_current_user_id() );
		// 更新する
		update_comment_meta( $ticket_id, '_participating', '' );
		// コメントがあれば追記
		$comment = $request->get_param( 'text' );
		if ( ! empty( $comment ) ) {
			$all_comment   = [ get_comment( $ticket_id )->comment_content ];
			$all_comment[] = sprintf( "%s: %s", date_i18n( 'Y/m/d H:i' ), $comment );
			wp_update_comment( [
				'comment_ID'      => $ticket_id,
				'comment_content' => implode( "\n\n---\n\n", array_filter( $all_comment ) ),
			] );
		}
		return new \WP_REST_Response( [
			'result'  => true,
			'message' => __( '参加を取り消しました。', 'hametuha' ),
		] );
	}

	public function permission_callback( $request ) {
		return current_user_can( 'read' );
	}
}
