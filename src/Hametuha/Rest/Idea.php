<?php

namespace Hametuha\Rest;


use Hametuha\Model\Ideas;
use Hametuha\Model\Notifications;
use Masterminds\HTML5\Serializer\HTML5Entities;
use WPametu\API\Rest\RestTemplate;

/**
 * Idea controller
 *
 * @package Hametuha\Rest
 * @property-read Notifications $notifications
 * @property-read Ideas $ideas
 */
class Idea extends RestTemplate {

	public static $prefix = 'my/ideas';

	protected $title = 'アイデア';

	protected $action = 'my-ideas';

	protected $content_type = 'text/html';

	protected $filtered = true;

	protected $models = [
		'ideas'         => Ideas::class,
		'notifications' => Notifications::class,
	];

	/**
	 * RestBase constructor.
	 *
	 * @param array $setting Setting value.
	 */
	public function __construct( array $setting ) {
		parent::__construct( $setting );
		// Add filter for Posts Request.
		add_filter( 'the_posts', [ $this, 'the_posts' ], 10, 2 );
	}

	/**
	 * Pager
	 *
	 * @param int $page Page number.
	 */
	protected function pager( $page = 1 ) {
		$this->auth_redirect();
		$this->title = 'アイデア帳';
		if ( 1 < $page ) {
			$this->method_not_found();
		} else {
			$this->set_data( [
				'breadcrumb' => $this->title,
			] );
			$this->load_template( 'templates/idea/base' );
		}
		exit;
	}

	/**
	 * Enqueue scripts and asset
	 *
	 * @param string $page Available only on admin screen
	 */
	public function enqueue_assets( $page = '' ) {
		$path = '/assets/js/dist/components/ideas.js';
		wp_enqueue_script( 'hametuha-follower', get_stylesheet_directory_uri() . $path, [
			'angular',
			'wp-api',
		], filemtime( get_stylesheet_directory() . $path ), true );
	}


	/**
	 * Detect if this is idea object.
	 *
	 * @param int $post_id
	 *
	 * @return bool|\WP_Error
	 */
	public function is_idea( $post_id ) {
		if ( ! is_numeric( $post_id ) || ! ( $post = get_post( $post_id ) ) || 'ideas' !== $post->post_type ) {
			return new \WP_Error( 'invalid_post_type', '該当するアイデアは存在しません。', [ 'status' => 403 ] );
		}

		return true;
	}

	/**
	 * Override this if you need rest API
	 */
	public function rest_api_init() {
		// Update
		register_rest_route( 'hametuha/v1', '/idea/mine/', [
			[
				'methods' => 'GET',
			    'callback' => [ $this, 'api_list' ],
			    'permission_callback' => function(){
				    return current_user_can( 'read' );
			    },
			    'args' => [
				    'offset' => [
					    'validate_callback' => 'is_numeric',
				        'default' => 0,
				    ],
			        's'      => [
				        'default' => '',
			        ],
			    ],
			],
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'api_create' ],
				'permission_callback' => function () {
					return current_user_can( 'read' );
				},
				'args'                => [
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
				],
			],
		    [
			    'methods' => 'PUT',
			    'callback'            => [ $this, 'api_edit' ],
			    'permission_callback' => function () {
				    return current_user_can( 'read' );
			    },
			    'args'                => [
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
			    ],
			],
		    [
				'methods' => 'DELETE',
				'callback' => [ $this, 'api_delete' ],
				'permission_callback' => function(){
					return current_user_can( 'read' );
				},
				'args' => [
					'post_id' => [
						'validate_callback' => [ $this, 'is_idea' ],
						'required' => true,
					],
				],
			],
		] );
		// Stock/unstock, recommend.
		register_rest_route( 'hametuha/v1', '/idea/(?<post_id>\\d+)/?', [
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'api_stock' ],
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
				'args'                => [
					'post_id' => [
						'validate_callback' => [ $this, 'is_idea' ],
						'required'          => true,
					],
				],
			],
			[
				'methods'             => 'PUT',
				'callback'            => [ $this, 'api_recommend' ],
				'permission_callback' => function () {
					return current_user_can( 'read' );
				},
				'args'                => [
					'post_id' => [
						'validate_callback' => [ $this, 'is_idea' ],
						'required'          => true,
					],
					'user_id' => [
						'validate_callback' => 'is_numeric',
						'required'          => true,
					],
				],
			],
			[
				'methods'             => 'DELETE',
				'callback'            => [ $this, 'api_trash' ],
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
				'args'                => [
					'post_id' => [
						'validate_callback' => [ $this, 'is_idea' ],
						'required'          => true,
					],
				],
			],
		] );
	}

	/**
	 * Get idea list
	 *
	 * @param array $params
	 *
	 * @return \WP_REST_Response
	 */
	public function api_list( $params ) {
		$results = $this->ideas->get_list( get_current_user_id(), $params['offset'], $params['s'] );
		foreach ( $results['ideas'] as &$result ) {
			$result->stocking     = $result->stocker == get_current_user_id() && $result->location == 1;
			$result->recommendor  = $result->recommended_by ? get_the_author_meta( 'display_name', $result->recommended_by ) : false;
			$result->own          = $result->post_author == get_current_user_id();
			$result->date         = mysql2date( get_option( 'date_format' ), $result->post_date );
			$result->permalink    = get_permalink( $result );
			$result->status       = get_post_status_object( $result->post_status )->label;
			$result->author       = get_the_author_meta( 'display_name', $result->post_author );
			$result->avatar       = preg_replace( '#^.*src=[\'"]([^\'"]+)[\'"].*$#', '$1', get_avatar( $result->post_author, 96 ) );
			$result->category     = implode(', ', array_map(function($term){
				return $term->name;
			}, get_the_tags( $result->ID ) ) );
		}
		return new \WP_REST_Response( $results );
	}

	/**
	 * Create new idea.
	 *
	 * @param array $params
	 *
	 * @return array|\WP_Error|\WP_Post|\WP_REST_Response
	 */
	public function api_create( $params ) {
		$post_id = wp_insert_post( [
			'post_type'    => 'ideas',
			'post_title'   => $params['title'],
			'post_status'  => $params['status'],
			'post_content' => $params['content'],
			'post_author'  => get_current_user_id(),
		] );
		if ( is_wp_error( $post_id ) ) {
			return new \WP_Error( 'failed_to_save_post', 'アイデアを保存できませんでした。後でやり直してください。', [ 'status' => 500 ] );
		}
		if ( is_wp_error( wp_set_object_terms( $post_id, intval( $params['genre'] ), 'post_tag' ) ) ) {
			return new \WP_Error( 'failed_to_save_term', 'アイデアは保存できましたが、ジャンル分けに失敗しました。', [ 'status' => 500 ] );
		}
		return new \WP_REST_Response( [
			'success' => true,
			'message' => 'アイデアを投稿しました。',
			'url'     => get_permalink( $post_id ),
		] );
	}

	/**
	 * Edit API endpoint.
	 *
	 * @param array $params
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function api_edit( $params ) {
		$post_id = wp_update_post( [
			'ID'           => $params['post_id'],
			'post_title'   => $params['title'],
			'post_status'  => $params['status'],
			'post_content' => $params['content'],
		], true );
		if ( is_wp_error( $post_id ) ) {
			return new \WP_Error( 'failed_to_save_post', 'アイデアを保存できませんでした。後でやり直してください。', [ 'status' => 500 ] );
		}
		if ( is_wp_error( wp_set_object_terms( $post_id, intval( $params['genre'] ), 'post_tag' ) ) ) {
			return new \WP_Error( 'failed_to_save_term', 'アイデアは保存できましたが、ジャンル分けに失敗しました。', [ 'status' => 500 ] );
		}

		return new \WP_REST_Response( [
			'success' => true,
			'message' => 'アイデアを更新しました。',
			'url'     => get_permalink( $post_id ),
		] );
	}

	/**
	 * Delete Idea
	 *
	 * @param array $params
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function api_delete( $params ) {
		if ( ! $this->is_idea( $params['post_id'] ) ) {
			return new \WP_Error( 'no_idea', '該当するアイデアは存在しません。', [ 'status' => 404 ] );
		}
		$idea = get_post( $params['post_id'] );
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

	/**
	 * Stock an idea.
	 *
	 * @param array $params
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function api_stock( $params ) {
		$post = get_post( $params['post_id'] );
		if ( $this->ideas->is_stocked( get_current_user_id(), $post->ID ) ) {
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
	 * Post idea
	 *
	 * @param array $params
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function api_trash( $params ) {
		$post = get_post( $params['post_id'] );
		if ( ! $this->ideas->score( get_current_user_id(), $post->ID ) ) {
			return new \WP_Error( 'duplicated', 'このアイデアは削除済みか、ストックしていません。', [ 'status' => 404 ] );
		}
		if ( ! $this->ideas->trash( get_current_user_id(), $post->ID ) ) {
			return new \WP_Error( 'server_error', '保存に失敗しました。あとでやり直してください。', [ 'status' => 500 ] );
		}

		return new \WP_REST_Response( [
			'message' => sprintf( 'アイデア: %s を却下しました。', $post->post_title ),
		] );
	}

	/**
	 * Add recommendation.
	 *
	 * @param array $params
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function api_recommend( $params ) {
		$post = get_post( $params['post_id'] );
		if ( ! ( $user = get_userdata( $params['user_id'] ) ) || ! $user->has_cap( 'edit_posts' ) ) {
			return new \WP_Error( 'not_found', '指定されたユーザーは存在しません。', [ 'status' => 404 ] );
		}
		if ( $post->post_author == $user->ID ) {
			return new \WP_Error( 'duplicated', sprintf( '%sさんはこのアイデアの作者です。', $post->post_title ), [ 'status' => 500 ] );
		}
		if ( $this->ideas->is_stocked( $user->ID, $post->ID ) ) {
			return new \WP_Error( 'duplicated', sprintf( '%sさんはこのアイデアを検討しているようです。', $post->post_title ), [ 'status' => 500 ] );
		}
		if ( ! $this->ideas->recommend( get_current_user_id(), $user->ID, $post->ID ) ) {
			return new \WP_Error( 'server_error', '保存に失敗しました。あとでやり直してください。', [ 'status' => 500 ] );
		}
		$current_user = get_userdata( get_current_user_id() );
		$this->notifications->add_notification( 'idea_recommended', $user->ID, $post->ID,
			sprintf( '%sさんから「%s」というアイデアが勧められています。', $current_user->display_name, $post->post_title ),
			get_current_user_id() );

		return new \WP_REST_Response( [
			'success' => true,
			'message' => 'アイデアをおすすめしました。',
		] );
	}

	/**
	 * Get POST recommend
	 *
	 * @param int $idea_id
	 */
	public function post_recommend( $idea_id ) {
		try {
			if ( ! current_user_can( 'read' ) ) {
				throw new \Exception( 'ログインしている必要があります。', 401 );
			}
			if ( is_wp_error( $this->is_idea( $idea_id ) ) ) {
				throw new \Exception( '該当するアイデアは存在しません。', 404 );
			}
			$response = [
				'success' => true,
				'html'    => hameplate('templates/form', 'recommend', [
					'idea' => get_post( $idea_id ),
				], false),
			];
		} catch ( \Exception $e ) {
			status_header( $e->getCode() );
			$response = [
				'success' => false,
				'message' => $e->getMessage(),
				'status'  => $e->getCode(),
			];
		}
		wp_send_json( $response );
	}

	/**
	 * Add new Idea
	 */
	public function post_new() {
		try {
			if ( ! current_user_can( 'read' ) ) {
				throw new \Exception( 'ログインしている必要があります。', 401 );
			}
			$response = [
				'success' => true,
				'html' => hameplate( 'templates/form', 'idea', [
					'id'      => 0,
					'title'   => '',
					'content' => '',
					'genre'   => 0,
					'private' => false,
				], false ),
			];
		} catch ( \Exception $e ) {
			status_header( $e->getCode() );
			$response = [
				'success' => false,
				'message' => $e->getMessage(),
				'status'  => $e->getCode(),
			];
		}
		wp_send_json( $response );
	}

	/**
	 * Get edit form
	 *
	 * @param int $post_id
	 */
	public function post_edit( $post_id ) {
		try {
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				throw new \Exception( '変更する権限がありません。', 401 );
			}
			if ( ! $this->is_idea( $post_id ) ) {
				throw new \Exception( '該当するアイデアは存在しません。', 404 );
			}
			$idea = get_post( $post_id );
			$response = [
				'success' => true,
				'html' => hameplate( 'templates/form', 'idea', [
					'id'      => $idea->ID,
					'title'   => $idea->post_title,
					'content' => $idea->post_content,
					'genre'   => implode( ',', array_map( function ( $term ) {
						return isset( $term->term_id ) ? $term->term_id : null;
					}, (array) get_the_tags( $idea->ID ) ) ),
					'private' => 'private' === $idea->post_status,
					'idea'    => get_post( $post_id ),
				], false ),
			];
		} catch ( \Exception $e ) {
			status_header( $e->getCode() );
			$response = [
				'success' => false,
				'message' => $e->getMessage(),
				'status'  => $e->getCode(),
			];
		}
		wp_send_json( $response );
	}

	/**
	 * Attach post's score.
	 *
	 * @param array $posts
	 * @param \WP_Query $wp_query
	 *
	 * @return mixed
	 */
	public function the_posts( $posts, \WP_Query $wp_query ) {
		if ( false !== array_search( $wp_query->get( 'post_type' ), [ '', 'any', 'ideas' ] ) ) {
			$posts_to_retrieve = [];
			foreach ( $posts as $post ) {
				if ( 'ideas' == $post->post_type ) {
					$posts_to_retrieve[] = $post->ID;
				}
			}
			if ( $posts_to_retrieve ) {
				$list = $this->ideas->get_stock_list( $posts_to_retrieve );
				foreach ( $posts as &$post ) {
					if ( isset( $list[ $post->ID ] ) ) {
						$post->stock = (int) $list[ $post->ID ];
					}
				}
			}
		}
		return $posts;
	}

}
