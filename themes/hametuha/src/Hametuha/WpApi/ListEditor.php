<?php

namespace Hametuha\WpApi;


use Hametuha\Model\Lists;
use WP_REST_Request;
use WP_REST_Response;
use WPametu\API\Rest\WpApi;

/**
 * リスト編集用のREST API
 *
 * @feature-group list
 * @property-read Lists $lists
 */
class ListEditor extends WpApi {

	/**
	 * @var array
	 */
	protected $models = [
		'lists' => Lists::class,
	];

	protected function get_route() {
		return 'lists/(?P<list_id>\d+|new|all)/?';
	}

	protected function get_arguments( $method ) {
		$args = [
			'list_id' => [
				'required' => true,
				'type'     => in_array( $method, [ 'GET', 'POST' ], true ) ? 'string' : 'integer',
				'validate_callback' => function ( $list_id ) {
					$post = get_post( $list_id );
					return ( $post && 'lists' === $post->post_type );
				}
			],
		];
		// POST, GETの場合はnewも受け入れる
		if ( in_array( $method, [ 'GET', 'POST' ], true ) ) {
			$args['list_id']['validate_callback'] = function( $list_id, WP_REST_Request $request ) {
				if ( 'new' === $list_id ) {
					// newの場合は新規作成
					return true;
				}
				if ( 'all' === $list_id && 'GET' === $request->get_method() ) {
					// GETの場合は新規作成を受け付ける
					return true;
				}
				// 数字の場合はリストが投稿として存在している必要がある。
				$post = get_post( $list_id );
				return ( $post && 'lists' === $post->post_type );
			};
		}
		// POSTの場合はタイトル、抜粋文、ステータスが必要
		if ( 'POST' === $method ) {
			$args['list_name'] = [
				'required' => true,
				'type'     => 'string',
				'validate_callback' => function ( $list_name ) {
					return ! empty( $list_name );
				},
			];
			$args['list_excerpt'] = [
				'required' => true,
				'type'     => 'string',
				'validate_callback' => function ( $list_excerpt) {
					return ! empty( $list_excerpt );
				},
			];
			$args['list_status'] = [
				'required' => true,
				'type'     => 'string',
				'validate_callback' => function ( $list_status ) {
					return in_array( $list_status , [ 'publish', 'private' ], true );
				},
			];
			$args['list_option'] = [
				'required' => false,
				'type'     => 'string',
				'default'  => '',
			];
		}
		// GETの場合は指定した投稿が含まれているかを受け入れる
		if ( 'GET' === $method ) {
			$args['includes'] = [
				'require'           => false,
				'type'              => 'integer',
				'default'           => 0,
				'validate_callback' => function ( $post_id ) {
					return is_numeric( $post_id );
				},
			];
		}
		// PUTの場合はステータスと投稿ID
		if ( 'PUT' === $method ) {
			$args['post_id'] = [
				'required' => true,
				'type'     => 'integer',
				'validate_callback' => function( $post_id ) {
					$post = get_post( $post_id );
					return ( $post && 'post' === $post->post_type );
				}
			];
			$args['action'] = [
				'required' => true,
				'type'     => 'string',
				'validate_callback' => function( $action ) {
					return in_array( $action, [ 'add', 'remove' ], true );
				}
			];
		}
		return $args;
	}

	/**
	 * リスト編集用のフォームを返す
	 *
	 * allだった場合はリストの一覧を返す
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	protected function handle_get( $request ) {
		if ( 'all' === $request['list_id'] ) {
			$query = new \WP_Query( [
				'post_type'      => 'lists',
				'my-content'     => 'lists',
				'posts_per_page' => 100,
				'orderby'        => [ 'date' => 'DESC' ],
				'no_found_rows'  => true,
			] );
			$response = [];
			foreach ( $query->posts as $post ) {
				$response[] = [
					'id'             => $post->ID,
					'title'          => get_the_title( $post ),
					'excerpt'        => $post->post_excerpt,
					'author'         => (int) $post->post_author,
					'status'         => $post->post_status,
					'url'            => get_permalink( $post ),
					'date'           => $post->post_date,
					'date_formatted' => get_the_time( get_option( 'date_format' ), $post ),
					'count'          => $post->num_children ?? 0,
					'recommended'    => $this->lists->is_recommended( $post->ID ),
				];
			}
			$includes = $request->get_param( 'includes' );
			if ( $includes && ! empty( $response ) ) {
				$includings = $this->lists->is_assigned_to( array_map( function( $list ) {
					return $list['id'];
				}, $response) , $includes );
				foreach ( $response as &$item ) {
					$item['includes'] = in_array( $item['id'], $includings, true );
				}
			}
			return new WP_REST_Response( $response );
		}
		ob_start();
		get_template_part( 'templates/list/form-list', '', [
			'list_id' => $request->get_param( 'list_id' ),
		] );
		$html = ob_get_clean();
		return new \WP_REST_Response( [
			'html' => $html,
		] );
	}

	/**
	 * リストの作成および編集
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_Error|\WP_REST_Response
	 */
	protected function handle_post( $request ) {
		// 編集用配列
		$posts_arr = [
			'post_title'   => $request->get_param( 'list_name' ),
			'post_excerpt' => $request->get_param( 'list_excerpt' ),
			'post_status'  => $request->get_param( 'list_status' ),
			'post_type'    => 'lists',
		];
		$post_id = $request->get_param( 'list_id' );
		if ( 'new' === $post_id ) {
			// 新規作成
			$posts_arr['post_author'] = get_current_user_id();
			$new_post_id = wp_insert_post( $posts_arr, true );
		} else {
			// 更新
			$posts_arr['ID'] = $post_id;
			$new_post_id = wp_update_post( $posts_arr, true );
		}
		// 失敗していたらエラー
		if ( is_wp_error( $new_post_id ) ) {
			return $new_post_id;
		}
		// オススメかどうか
		// todo: 編集者のみおすすめにできる、という条件がこれでよかったか？
		if ( current_user_can( 'edit_others_posts' ) && 'recommended' === $request->get_param( 'list_option' ) ) {
			$this->lists->mark_as_recommended( $new_post_id );
		} else {
			$this->lists->not_recommended( $new_post_id );
		}
		return new WP_REST_Response( [
			'success' => true,
			'post' => [
				'ID' => $new_post_id,
			],
		] );
	}

	/**
	 * リストの削除
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_Error|\WP_REST_Response
	 */
	protected function handle_delete( $request ) {
		$list_id = $request->get_param( 'list_id' );
		$title   = get_the_title( $list_id );
		if ( ! wp_delete_post( $list_id, true ) ) {
			return new \WP_Error( 'invalid_list_edit', __( '申し訳ございません、指定されたリストを削除できませんでした。', 'hametuha' ) );
		}
		return new \WP_REST_Response( [
			'success' => true,
			'message' => sprintf( __( 'リスト「%s」を削除しました。', 'hametuha' ), $title ),
			'url'     => home_url( '/your/lists/' ),
		] );
	}

	/**
	 * リストに含める投稿の編集
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_Error|\WP_REST_Response
	 */
	protected function handle_put( $request ) {
		$list_id = $request->get_param( 'list_id' );
		$post_id = $request->get_param( 'post_id' );
		switch ( $request->get_param( 'action' ) ) {
			case 'add':
				$result = $this->lists->register( $list_id, $post_id );
				if ( ! $result ) {
					return new \WP_Error( 'invalid_list_edit', 'リストに追加できませんでした。あとでやり直してください。' );
				}
				return new \WP_REST_Response( [
					'success'  => true,
					'message'  => sprintf( 'リストに「%s」を追加しました。', get_the_title( $post_id ) ),
					'list_url' => get_permalink( $list_id ),
					'home_url' => home_url( '/your/lists/' ),
				] );
				break;
			case 'remove':
				// 削除する
				try {
					$this->lists->deregister( $list_id, $post_id );
					return new \WP_REST_Response( [
						'success'  => true,
						'message'  => sprintf( 'リストから「%s」を削除しました。', get_the_title( $post_id ) ),
						'list_url' => get_permalink( $list_id ),
						'home_url' => home_url( '/your/lists/' ),
					] );
				} catch ( \Exception $e ) {
					return new \WP_Error( 'invalid_list_edit', 'リストから削除できませんでした。あとでやり直してください。' );
				}
			default:
				return new \WP_Error( 'invalid_action', '', [
					'status' => 400,
				] );
		}
	}

	/**
	 * パーミッションを確認する
	 *
	 * 新規作成およびリストへの登録はログイン済み
	 * 編集・削除は編集権限が必要
	 *
	 * @param \WP_REST_Request $request
	 * @return bool
	 */
	public function permission_callback( $request ) {
		switch ( strtoupper( $request->get_method() ) ) {
			case 'GET':
			case 'POST':
				if ( in_array( $request->get_param( 'list_id' ), [ 'new', 'all' ], true ) ) {
					return current_user_can( 'read' );
				}
				return current_user_can( 'edit_post', $request->get_param( 'list_id' ) );
			default:
				// デフォルトは編集権限必須
				return current_user_can( 'edit_post', $request->get_param( 'list_id' ) );
		}
	}
}
