<?php

namespace Hametuha\WpApi;


use WPametu\API\Rest\WpApi;

/**
 * 合評会 当日採点 API
 *
 * GET  : 状態・自分の配分・進捗を返す
 * POST : 自分の配分を保存する（当日参加者のみ）
 *
 * @package Hametuha\WpApi
 * @feature-group joint-review
 */
class JointReviewScore extends WpApi {

	/**
	 * {@inheritDoc}
	 */
	protected function get_route() {
		return 'campaign/joint-review/(?P<term_id>\d+)/?';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_arguments( $method ) {
		$args = [
			'term_id' => [
				'type'              => 'integer',
				'description'       => 'Campaign term ID',
				'required'          => true,
				'validate_callback' => function ( $term_id ) {
					$term = get_term( $term_id, 'campaign' );
					return $term && ! is_wp_error( $term );
				},
			],
		];
		if ( 'POST' === $method ) {
			$args['scores'] = [
				'type'        => 'object',
				'description' => '作品IDをキー、配点を値とするオブジェクト',
				'required'    => true,
			];
		}
		return $args;
	}

	/**
	 * 状態・自分の配分・進捗を返す
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response
	 */
	public function handle_get( \WP_REST_Request $request ) {
		$term     = get_term( $request->get_param( 'term_id' ), 'campaign' );
		$user_id  = get_current_user_id();
		$response = [
			'state'          => hametuha_jr_state( $term ),
			'allotment'      => hametuha_jr_allotment( $term ),
			'voted'          => count( hametuha_jr_voters( $term ) ),
			'total'          => count( hametuha_jr_participants( $term ) ),
			'is_participant' => hametuha_jr_is_participant( $term, $user_id ),
		];
		if ( $response['is_participant'] ) {
			$response['distribution'] = (object) hametuha_jr_user_distribution( $term, $user_id );
		}
		return new \WP_REST_Response( $response );
	}

	/**
	 * 自分の配分を保存する
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function handle_post( \WP_REST_Request $request ) {
		$term = get_term( $request->get_param( 'term_id' ), 'campaign' );
		if ( 'open' !== hametuha_jr_state( $term ) ) {
			return new \WP_Error( 'jr_closed', __( '現在この合評会は採点を受け付けていません。', 'hametuha' ), [ 'status' => 403 ] );
		}
		$user_id = get_current_user_id();
		if ( ! hametuha_jr_is_participant( $term, $user_id ) ) {
			return new \WP_Error( 'jr_not_participant', __( '採点できるのは当日参加者のみです。', 'hametuha' ), [ 'status' => 403 ] );
		}
		$scores = (array) $request->get_param( 'scores' );
		$dist   = [];
		foreach ( $scores as $post_id => $point ) {
			$dist[ (int) $post_id ] = (float) $point;
		}
		$result = hametuha_jr_save_distribution( $term, $user_id, $dist );
		if ( is_wp_error( $result ) ) {
			$result->add_data( [ 'status' => 400 ] );
			return $result;
		}
		return new \WP_REST_Response( [
			'success' => true,
			'message' => __( '採点を保存しました。', 'hametuha' ),
			'voted'   => count( hametuha_jr_voters( $term ) ),
			'total'   => count( hametuha_jr_participants( $term ) ),
		] );
	}

	/**
	 * ログインユーザーのみ
	 *
	 * @param \WP_REST_Request $request
	 * @return bool
	 */
	public function permission_callback( $request ) {
		return is_user_logged_in();
	}
}
