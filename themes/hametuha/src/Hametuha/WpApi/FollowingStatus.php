<?php

namespace Hametuha\WpApi;


use Hametuha\WpApi\Pattern\FollowPattern;

/**
 * フォローしたり外したりするエンドポイント
 *
 */
class FollowingStatus extends FollowPattern {

	protected function get_route() {
		return 'doujin/follow/(?<id>\d+)/?';
	}

	protected function get_arguments( $method ) {
		return [
			'id' => [
				'required'          => true,
				'validate_callback' => [ $this, 'is_user_id' ],
			],
		];
	}

	/**
	 * Add follower
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function handle_post( $request ) {
		$user_id   = get_current_user_id();
		$target_id = $request['id'];
		$error     = $this->follower->follow( $user_id, $target_id );
		if ( is_wp_error( $error ) ) {
			return $error;
		} else {
			$user = get_userdata( get_current_user_id() );
			$msg  = sprintf( '%sさんがあなたをフォローしました', $user->nickname );
			$this->notifications->add_follow( $target_id, $user_id, $msg, $user_id );
			return new \WP_REST_Response( [ 'success' => true ] );
		}
	}

	/**
	 * Unfollow
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function handle_delete( $request ) {
		$user_id   = get_current_user_id();
		$target_id = $request['id'];
		$error     = $this->follower->unfollow( $user_id, $target_id );
		if ( is_wp_error( $error ) ) {
			return $error;
		} else {
			return new \WP_REST_Response( [ 'success' => true ] );
		}
	}
}
