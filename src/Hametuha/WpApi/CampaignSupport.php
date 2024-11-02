<?php

namespace Hametuha\WpApi;


use Hametuha\Hooks\CampaignController;
use WPametu\API\Rest\WpApi;

/**
 * Campaign support API
 *
 * @package Hametuha\WpApi
 */
class CampaignSupport extends WpApi {

	protected function get_route() {
		return 'campaign/support/(?P<term_id>\d+)/?';
	}

	protected function get_arguments( $method ) {
		return [
			'term_id' => [
				'type'        => 'integer',
				'description' => 'Campaign ID',
				'required'    => true,
				'validate_callback' => function( $term_id ) {
					$term = get_term( $term_id, 'campaign' );
					if ( ! $term || is_wp_error( $term ) ) {
						return false;
					}
					// そもそもサポート可能か？
					if ( ! get_term_meta( $term_id, '_is_collaboration', true ) ) {
						return new \WP_Error( 'campaign_support_error', __( 'この公募はサポートできません。', 'hametuha' ) );
					}
					// 締切を過ぎていないか？
					if ( ! hametuha_is_available_campaign( $term ) ) {
						return new \WP_Error( 'campaign_support_error', __( 'この公募は締め切られています。', 'hametuha' ) );
					}
					return true;
				},
			],
		];
	}

	/**
	 * Support
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function handle_post( \WP_REST_Request $request ) {
		$term = get_term( $request->get_param( 'term_id' ), 'campaign' );
		// すでに参加していたらエラー
		if ( $this->controller()->is_user_supporting( $term, get_current_user_id() ) ) {
			return new \WP_Error( 'campaign_support_error', __( 'すでにこの公募をサポートしています。', 'hametuha' ) );
		}
		// サポートする
		if ( ! add_user_meta( get_current_user_id(), 'supporting_campaigns', $term->term_id ) ) {
			return new \WP_Error( 'campaign_support_error', __( 'サポート登録をできませんでした。', 'hametuha' ) );
		}
		do_action( 'hametuha_campaign_supporter_add', $term, get_current_user_id() );
		return new \WP_REST_Response( [
			'success' => true,
			'url'     => get_term_link( $term ),
		] );
	}

	/**
	 * Remove Support
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function handle_delete( \WP_REST_Request $request ) {
		$term = get_term( $request->get_param( 'term_id' ), 'campaign' );
		// 参加していなかったらエラー
		if ( ! $this->controller()->is_user_supporting( $term, get_current_user_id() ) ) {
			return new \WP_Error( 'campaign_support_error', __( 'この公募をサポートしていません。', 'hametuha' ) );
		}
		if ( ! delete_user_meta( get_current_user_id(), 'supporting_campaigns', $term->term_id ) ) {
			return new \WP_Error( 'campaign_support_error', __( 'サポートを取り消せませんでした。', 'hametuha' ) );
		}
		do_action( 'hametuha_campaign_supporter_removed', $term, get_current_user_id() );
		return new \WP_REST_Response( [
			'success' => true,
			'url'     => get_term_link( $term ),
		] );
	}

	/**
	 * Is user participating in campaign?
	 *
	 * @param \WP_REST_Request $request
	 * @return bool
	 */
	public function permission_callback( $request ) {
		return is_user_logged_in();
	}

	/**
	 * コントローラーへのアクセス
	 *
	 * @return CampaignController
	 */
	protected function controller() {
		return CampaignController::get_instance();
	}
}
