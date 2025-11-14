<?php

namespace Hametuha\WpApi;


use Hametuha\Model\Anpis;
use Hametuha\Model\Notifications;
use WPametu\API\Rest\WpApi;

/**
 * 安否情報用の
 *
 * @property-read Anpis $anpis
 * @property-read Notifications $notifications
 */
class Anpi extends WpApi {

	protected $models = [
		'anpis'         => Anpis::class,
		'notifications' => Notifications::class,
	];

	protected function get_route() {
		return 'anpi/new/?';
	}

	protected function get_arguments( $method ) {
		return [
			'content' => [
				'type'              => 'string',
				'required'          => true,
				'validate_callback' => function ( $var ) {
					return ! empty( $var );
				},
			],
		];
	}

	/**
	 * 安否情報を新規作成する
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function handle_post( $request ) {
		$content = $request->get_param( 'content' );
		$user_id = get_current_user_id();
		$result  = $this->anpis->create_tweet( $user_id, $content );
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		return new \WP_REST_Response( [
			'success' => true,
			'message' => __( '安否報告を受け付けました。', 'hametuha' ),
			'post'    => $this->post_to_response( get_post( $result ) ),
		] );
	}

	/**
	 * 安否情報の投稿オブジェクトを配列に変換する
	 *
	 * @param \WP_Post $post
	 *
	 * @return array{}
	 */
	protected function post_to_response( $post ) {
		$terms        = [];
		$term_objects = get_terms( 'anpi_cat', [ 'hide_empty' => false ] );
		if ( $term_objects && ! is_wp_error( $term_objects ) ) {
			foreach ( $term_objects as $term ) {
				$terms[] = [
					'id'     => $term->term_id,
					'name'   => $term->name,
					'active' => has_term( $term->term_id, 'anpi_cat', $post ),
				];
			}
		}

		return [
			'id'         => $post->ID,
			'type'       => 'anpi',
			'status'     => $post->post_status,
			'title'      => $post->post_title,
			'url'        => get_permalink( $post ),
			'date'       => get_gmt_from_date( $post->post_date, DATE_ISO8601 ),
			'modified'   => get_gmt_from_date( $post->post_modified, DATE_ISO8601 ),
			'categories' => $terms,
			'content'    => $post->post_content,
		];
	}

	public function permission_callback( $request ) {
		return current_user_can( 'read' );
	}
}
