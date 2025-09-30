<?php

namespace Hametuha\WpApi;


use Hametuha\WpApi\Pattern\FollowPattern;

/**
 * フォロワーの一覧を返すエンドポイント
 */
class Followers extends FollowPattern {

	protected function get_route() {
		return 'doujin/(?P<action>followers|following)/(?P<id>\+d|me)';
	}

	protected function get_arguments( $method ) {
		return [
			'action' => [
				'type'              => 'string',
				'required'          => true,
				'validate_callback' => function ( $var ) {
					return in_array( $var, [ 'following', 'followers' ], true );
				},
			],
			'id'     => [
				'type'              => 'string',
				'required'          => true,
				'validate_callback' => [ $this, 'id_or_me' ],
			],
			'offset' => [
				'type'              => 'integer',
				'validate_callback' => function ( $var ) {
					return is_numeric( $var );
				},
				'default'           => 0,
			],
			's'      => [
				'type'    => 'string',
				'default' => '',
			],
		];
	}

	/**
	 * ユーザーのリストを返す
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	protected function handle_get( $request ) {
		$user_id = $request->get_param( 'id' );
		if ( 'me' === $user_id ) {
			$user_id = get_current_user_id();
		} elseif ( get_current_user_id() !== (int) $user_id && ! current_user_can( 'list_users' ) ) {
			// list userできなくてなおかつ自分じゃなかったらエラー
			return new \WP_Error( 'invalid_user_list', __( '自分以外のフォロー状況は確認できません。', 'hametuha' ), [
				'status' => 403,
			] );
		}
		$s      = $request->get_param( 's' );
		$offset = $request->get_param( 'offset' );
		switch ( $request->get_param( 'action' ) ) {
			case 'following':
				$result = $this->follower->get_following( $user_id, $offset, $s );
				break;
			case 'followers':
				$result = $this->follower->get_followers( $user_id, $offset, $s );
				break;
			default:
				$result = [
					'users' => [],
					'total' => 0,
				];
				break;
		}
		// マップする
		$result['users'] = array_map( [ $this, 'sanitize_user' ], $result['users'] );
		return new \WP_REST_Response( $result );
	}
}
