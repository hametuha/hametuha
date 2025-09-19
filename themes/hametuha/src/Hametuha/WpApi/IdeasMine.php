<?php

namespace Hametuha\WpApi;


use Hametuha\WpApi\Pattern\IdeaApiPattern;

/**
 * 自分のアイデアに関するREST API
 */
class IdeasMine extends IdeaApiPattern {


	protected function get_route() {
		return 'idea/mine/?';
	}

	protected function get_arguments( $method ) {
		switch ( $method ) {
			case 'GET':
				return [
					'offset' => [
						'validate_callback' => 'is_numeric',
						'default'           => 0,
					],
					's'      => [
						'default' => '',
					],
				];
			case 'POST':
				return [
					'title'   => [
						'validate_callback' => function ( $var ) {
							return ! empty( $var );
						},
						'required'          => true,
					],
					'content' => [
						'validate_callback' => function ( $var ) {
							return ! empty( $var );
						},
						'required'          => true,
					],
					'status'  => [
						'validate_callback' => function ( $status ) {
							switch ( $status ) {
								case 'private':
								case 'publish':
									return true;
									break;
								default:
									return false;
									break;
							}
						},
					],
					'genre'   => [
						'validate_callback' => function ( $var ) {
							$term = get_tag( $var );

							return $term && ! is_wp_error( $term ) && ( 'idea' === get_term_meta( $term->term_id, 'tag_type', true ) );
						},
						'required'          => true,
					],
				];
			case 'PUT':
				return [
					'post_id' => [
						'validate_callback' => [ $this, 'is_idea' ],
						'required'          => true,
					],
					'title'   => [
						'validate_callback' => function ( $var ) {
							return ! empty( $var );
						},
						'required'          => true,
					],
					'content' => [
						'validate_callback' => function ( $var ) {
							return ! empty( $var );
						},
						'required'          => true,
					],
					'status'  => [
						'validate_callback' => function ( $status ) {
							switch ( $status ) {
								case 'private':
								case 'publish':
									return true;
									break;
								default:
									return false;
									break;
							}
						},
					],
					'genre'   => [
						'validate_callback' => function ( $var ) {
							$term = get_tag( $var );

							return $term && ! is_wp_error( $term ) && ( 'idea' === get_term_meta( $term->term_id, 'tag_type', true ) );
						},
						'required'          => true,
					],
				];
			case 'DELETE':
				return [
					'post_id' => [
						'validate_callback' => [ $this, 'is_idea' ],
						'required'          => true,
					],
				];
			default:
				return [];
		}
	}

	/**
	 * アイデアを取得する
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	protected function handle_get( $request ) {
		$results = $this->ideas->get_list( get_current_user_id(), $request['offset'], $request['s'] );
		foreach ( $results['ideas'] as &$result ) {
			$result->stocking    = $result->stocker == get_current_user_id() && $result->location == 1;
			$result->recommendor = $result->recommended_by ? get_the_author_meta( 'display_name', $result->recommended_by ) : false;
			$result->own         = $result->post_author == get_current_user_id();
			$result->date        = mysql2date( get_option( 'date_format' ), $result->post_date );
			$result->permalink   = get_permalink( $result );
			$result->status      = get_post_status_object( $result->post_status )->label;
			$result->author      = get_the_author_meta( 'display_name', $result->post_author );
			$result->avatar      = preg_replace( '#^.*src=[\'"]([^\'"]+)[\'"].*$#', '$1', get_avatar( $result->post_author, 96 ) );
			$result->category    = implode(', ', array_map(function( $term ) {
				return $term->name;
			}, get_the_tags( $result->ID ) ) );
		}
		return new \WP_REST_Response( $results );
	}

	/**
	 * アイデアを取得する
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	protected function handle_post( $request ) {
		$post_id = wp_insert_post( [
			'post_type'    => 'ideas',
			'post_title'   => $request['title'],
			'post_status'  => $request['status'],
			'post_content' => $request['content'],
			'post_author'  => get_current_user_id(),
		] );
		if ( is_wp_error( $post_id ) ) {
			return new \WP_Error( 'failed_to_save_post', 'アイデアを保存できませんでした。後でやり直してください。', [ 'status' => 500 ] );
		}
		if ( is_wp_error( wp_set_object_terms( $post_id, intval( $request['genre'] ), 'post_tag' ) ) ) {
			return new \WP_Error( 'failed_to_save_term', 'アイデアは保存できましたが、ジャンル分けに失敗しました。', [ 'status' => 500 ] );
		}
		return new \WP_REST_Response( [
			'success' => true,
			'message' => 'アイデアを投稿しました。',
			'url'     => get_permalink( $post_id ),
		] );
	}

	/**
	 * アイデアを取得する
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	protected function handle_put( $request ) {
		$post_id = wp_update_post( [
			'ID'           => $request['post_id'],
			'post_title'   => $request['title'],
			'post_status'  => $request['status'],
			'post_content' => $request['content'],
		], true );
		if ( is_wp_error( $post_id ) ) {
			return new \WP_Error( 'failed_to_save_post', 'アイデアを保存できませんでした。後でやり直してください。', [ 'status' => 500 ] );
		}
		if ( is_wp_error( wp_set_object_terms( $post_id, intval( $request['genre'] ), 'post_tag' ) ) ) {
			return new \WP_Error( 'failed_to_save_term', 'アイデアは保存できましたが、ジャンル分けに失敗しました。', [ 'status' => 500 ] );
		}

		return new \WP_REST_Response( [
			'success' => true,
			'message' => 'アイデアを更新しました。',
			'url'     => get_permalink( $post_id ),
		] );
	}

	/**
	 * アイデアを取得する
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	protected function handle_delete( $request ) {
		if ( ! $this->is_idea( $request['post_id'] ) ) {
			return new \WP_Error( 'no_idea', '該当するアイデアは存在しません。', [ 'status' => 404 ] );
		}
		$idea = get_post( $request['post_id'] );
		if ( ! current_user_can( 'edit_post', $idea->ID ) ) {
			return new \WP_Error( 'no_permission', 'あなたには削除する権利がありません。', [ 'status' => 401 ] );
		}
		if ( $this->ideas->get_stock_count( $idea->ID ) ) {
			return new \WP_Error( 'no_permission', 'このアイデアをストックしている人がいるので、削除できません。', [ 'status' => 403 ] );
		}
		$deleted = wp_delete_post( $idea->ID );
		if ( false === $deleted ) {
			return new \WP_Error( 'failed_to_save', 'アイデアの削除に失敗しました。', [ 'status' => 503 ] );
		}

		return new \WP_REST_Response( [
			'success' => true,
			'message' => sprintf( 'アイデア「%s」を削除しました。', $deleted->post_title ),
		] );
	}
}
