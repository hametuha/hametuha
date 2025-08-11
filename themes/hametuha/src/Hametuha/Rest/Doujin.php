<?php

namespace Hametuha\Rest;


use Hametuha\Interfaces\OgpCustomizer;
use Hametuha\Model\Author;
use Hametuha\Model\Collaborators;
use Hametuha\Model\Follower;
use Hametuha\Model\Notifications;
use Hametuha\Model\Review;
use Hametuha\Model\Series;
use Hametuha\ThePost\Announcement;
use WPametu\API\Rest\RestTemplate;

/**
 * Class Profile
 * @package Hametuha\Rest
 * @property-read Series $series
 * @property-read Author $author
 * @property-read Review $review
 * @property-read Follower $follower
 * @property-read Notifications $notifications
 */
class Doujin extends RestTemplate implements OgpCustomizer {

	public static $prefix = 'doujin';

	protected $title = '同人';

	protected $action = 'doujin';

	protected $content_type = 'text/html';

	/**
	 * @var \WP_User|false
	 */
	public $doujin = null;

	/**
	 * @var array
	 */
	protected $models = [
		'series'        => Series::class,
		'author'        => Author::class,
		'review'        => Review::class,
		'follower'      => Follower::class,
		'notifications' => Notifications::class,
	];

	/**
	 * Override this if you need rest API
	 */
	public function rest_api_init() {
		register_rest_route( 'hametuha/v1', '/doujin/followers/(?P<id>\\d+|me)/?', [
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'api_followers' ],
				'args'                => [
					'id'     => [
						'validate_callback' => function ( $var ) {
							return 'me' === $var || is_numeric( $var );
						},
						'default'           => 0,
					],
					'offset' => [
						'validate_callback' => function( $var ) {
							return is_numeric( $var );
						},
						'default'           => 0,
					],
					's'      => [
						'default' => '',
					],
				],
				'permission_callback' => function () {
					return current_user_can( 'read' );
				},
			],
		] );
		register_rest_route( 'hametuha/v1', '/doujin/following/(?P<id>\\d+|me)/?', [
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'api_following' ],
				'args'                => [
					'id'     => [
						'validate_callback' => function ( $var ) {
							return 'me' === $var || is_numeric( $var );
						},
						'default'           => 0,
					],
					'offset' => [
						'validate_callback' => function( $var ) {
							return is_numeric( $var );
						},
						'default'           => 0,
					],
					's'      => [
						'default' => '',
					],
				],
				'permission_callback' => function () {
					return current_user_can( 'read' );
				},
			],
		] );
		// Follow/Unfollow.
		register_rest_route( 'hametuha/v1', '/doujin/follow/(?P<id>\d+)/?', [
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'api_add_follower' ],
				'args'                => [
					'id' => [
						'required'          => true,
						'validate_callback' => function( $var ) {
							return is_numeric( $var );
						},
					],
				],
				'permission_callback' => function () {
					return current_user_can( 'read' );
				},
			],
			[
				'methods'             => 'DELETE',
				'callback'            => [ $this, 'api_remove_follower' ],
				'args'                => [
					'id' => [
						'required'          => true,
						'validate_callback' => function( $var ) {
							return is_numeric( $var );
						},
					],
				],
				'permission_callback' => function () {
					return current_user_can( 'read' );
				},
			],
		] );
		// Follow/Unfollow.
		register_rest_route( 'hametuha/v1', '/doujin/search/(?P<mode>any|friends|authors)/?', [
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'api_search_user' ],
				'args'                => [
					'mode' => [
						'required'          => true,
						'validate_callback' => function( $var ) {
							return false !== array_search( $var, [ 'any', 'friends', 'authors' ] );
						},
					],
					's'    => [
						'required' => true,
					],
				],
				'permission_callback' => function ( $request ) {
					switch ( $request['mode'] ) {
						case 'friends':
							return current_user_can( 'read' );
							break;
						case 'authors':
							return true;
							break;
						default:
							return current_user_can( 'list_users' );
							break;
					}
				},
			],
		] );

		// Event Participant
		register_rest_route( 'hametuha/v1', '/participants/(?P<post_id>\d+)/?', [
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'api_participant_status' ],
				'args'                => [
					'post_id' => [
						'required'          => true,
						'validate_callback' => function( $var ) {
							return $var && is_numeric( $var );
						},
					],
					'status'  => [
						'required' => true,
					],
					'text'    => [
						'default' => '',
					],
				],
				'permission_callback' => function ( $request ) {
					return current_user_can( 'read' );
				},
			],
		] );
	}

	/**
	 * Add follower
	 *
	 * @param array $request
	 *
	 * @return bool|\WP_Error|\WP_REST_Response
	 */
	public function api_add_follower( $request ) {
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
	 * @param array $request
	 *
	 * @return bool|\WP_Error|\WP_REST_Response
	 */
	public function api_remove_follower( $request ) {
		$user_id   = get_current_user_id();
		$target_id = $request['id'];
		$error     = $this->follower->unfollow( $user_id, $target_id );
		if ( is_wp_error( $error ) ) {
			return $error;
		} else {
			return new \WP_REST_Response( [ 'success' => true ] );
		}
	}

	/**
	 * Search user
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function api_search_user( $request ) {
		switch ( $request['mode'] ) {
			case 'friends':
				$users = [];
				break;
			default:
				$users = array_map( [
					$this,
					'process_user_data',
				], $this->author->search( $request['s'], 'any' != $request['mode'] ) );
				break;
		}
		return new \WP_REST_Response( $users );
	}

	/**
	 * Handler event participation
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function api_participant_status( $request ) {
		$post = get_post( $request['post_id'] );
		if ( ! $post || ( false === array_search( $post->post_type, [ 'announcement', 'news' ] ) ) ) {
			return new \WP_Error( 404, '該当するイベントが存在しません', [ 'status' => 404 ] );
		}
		$event = new Announcement( $post );
		if ( ! $event->is_participating() ) {
			return new \WP_Error( 400, 'このイベントはすでに参加期限を過ぎています。', [ 'status' => 400 ] );
		}
		if ( $event->participating_limit() <= $event->participating_count() ) {
			return new \WP_Error( 400, 'すでに定員に達しています。', [ 'status' => 400 ] );
		}
		$args = [
			'comment_type'     => 'participant',
			'comment_post_ID'  => $request['post_id'],
			'comment_content'  => $request['text'],
			'comment_approved' => 1,
			'user_id'          => get_current_user_id(),
		];
		if ( $comment_id = $event->get_ticket_id( get_current_user_id() ) ) {
			$args['comment_ID'] = $comment_id;
			wp_update_comment( $args );
			$updated = true;
		} else {
			$comment_id = wp_insert_comment( $args );
			$updated    = false;
		}
		update_comment_meta( $comment_id, '_participating', $request['status'] );
		if ( $comment_id ) {
			$organizer = get_userdata( $post->post_author );
			if ( get_current_user_id() != $post->post_author ) {
				do_action( 'hametuha_notification', 'participant', "参加状況: {$post->post_title}", $organizer->user_email, [
					'post'        => $post,
					'status'      => $request['status'],
					'organizer'   => $organizer,
					'participant' => get_userdata( get_current_user_id() ),
					'update'      => $updated,
					'message'     => $request['text'],
				] );
			}
			return new \WP_REST_Response( $event->get_user_object( $comment_id ) );
		} else {
			return new \WP_Error( 500, 'ステータスの変更に失敗しました。', [
				'status' => 500,
			] );
		}
	}

	/**
	 * Process user data
	 *
	 * @param \WP_User $user
	 * @param string $context
	 * @return array
	 */
	protected function process_user_data( $user, $context = 'api' ) {
		$user_data = [
			'ID'          => $user->ID,
			'name'        => $user->display_name,
			'avatar'      => get_avatar_url( $user->ID, [
				'size' => 96,
			] ),
			'role'        => hametuha_user_role( $user ),
			'profile_url' => $user->has_cap( 'edit_posts' ) ? home_url( "/doujin/detail/{$user->user_nicename}/" ) : '',
		];
		/**
		 * ユーザーのデータを返すフィルター
		 */
		return apply_filters( 'hametuha_api_user', $user_data, $context );
	}

	/**
	 * Get followers
	 *
	 * @param array $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function api_followers( $request ) {
		$user_id = $request['id'];
		if ( 'me' === $user_id ) {
			$user_id = get_current_user_id();
		}
		if ( ! $user_id ) {
			if ( is_user_logged_in() ) {
				$user_id = get_current_user_id();
			} else {
				return new \WP_Error( 'no_user', '指定されたユーザーは存在しません。', [ 'response' => 404 ] );
			}
		}
		$result = $this->follower->get_followers( $user_id, $request['offset'], $request['s'] );
		foreach ( $result['users'] as &$user ) {
			$user = $this->sanitize_user( $user );
		}

		return new \WP_REST_Response( $result );
	}

	/**
	 * Return following user
	 *
	 * @param array $request
	 *
	 * @return \WP_REST_Response
	 */
	public function api_following( $request ) {
		$user_id = $request['id'];
		if ( 'me' === $user_id ) {
			$user_id = get_current_user_id();
		}
		$result = $this->follower->get_following( $user_id, $request['offset'], $request['s'] );
		foreach ( $result['users'] as &$user ) {
			$user = $this->sanitize_user( $user );
		}

		return new \WP_REST_Response( $result );
	}

	/**
	 * Sanitize User
	 *
	 * @param \stdClass|\WP_User $user User object.
	 * @param bool $additional Default true.
	 *
	 * @return \stdClass|\WP_User
	 */
	protected function sanitize_user( $user, $additional = true ) {
		// Additional informtion
		if ( $additional ) {
			$user->isAuthor = user_can( $user->ID, 'edit_posts' );
			$user->isEditor = user_can( $user->ID, 'edit_others_posts' );
			$user->avatar   = preg_replace( '#^.*src=[\'"]([^\'"]+)[\'"].*$#', '$1', get_avatar( $user->ID, 96 ) );
		}
		// Remove credentials.
		unset( $user->user_email );
		unset( $user->user_pass );
		unset( $user->user_activation_key );

		return $user;

	}

	/**
	 * 取得すべきユーザーを設定
	 *
	 * @param string $nice_name
	 *
	 * @throws \Exception
	 */
	protected function set_member( $nice_name = '' ) {
		$this->doujin = $this->author->get_by_nice_name( $nice_name );
		if ( ! $this->doujin || ! $this->doujin->has_cap( 'edit_posts' ) ) {
			$this->wp_query->set( 'p', -1 );
			throw new \Exception( 'Page Not Found.', 404 );
		}
	}

	/**
	 * ポータルページ
	 *
	 * @param string $author
	 */
	protected function pager( $author = '' ) {
		// Bypass
		$this->method_not_found();
	}

	/**
	 * Get doujin detail
	 *
	 * @param $author_name
	 */
	public function get_detail( $author_name ) {
		if ( 1 < count( func_get_args() ) ) {
			// 指定が多すぎるのでエラーを返す。
			$this->wp_query->set( 'p', -1 );
			$this->method_not_found();
		}
		// メンバーをセット、いなければエラー。
		$this->set_member( $author_name );
		$this->title = $this->doujin->display_name . 'のプロフィール | ' . get_bloginfo( 'name' );
		// カノニカルを設定
		add_action( 'wp_head', function() use ( $author_name ) {
			echo hametuha_canonical( home_url( "doujin/detail/{$author_name}/" ) );
		}, 1 );
		$this->set_data( [
			'breadcrumb' => false,
			'current'    => false,
			'template'   => '',
			'reviews'    => $this->get_review_json(),
		] );
		$this->response();
	}

	/**
	 * フォロワー一覧
	 */
	public function get_follower() {
		nocache_headers();
		$this->auth_redirect();
		$this->doujin = new \WP_User( get_current_user_id() );
		$this->title  = 'フォロワー | 破滅派';
		$this->set_data( [
			'breadcrumb' => false,
			'current'    => false,
			'template'   => 'follower',
		] );
		$this->response();
	}

	/**
	 * Get review jSON
	 *
	 * @return array
	 */
	protected function get_review_json() {
		$data = $this->review->get_author_chart_points( $this->doujin->ID );

		return $data;
	}

	/**
	 * Set OGP
	 *
	 * @param array $values 'image', 'title, 'url', 'type', 'desc', 'card', 'author'
	 *
	 * @return array
	 */
	public function ogp( array $values ) {
		if ( $this->doujin ) {
			$values[ 'url' ] = home_url( '/doujin/detail/' . $this->doujin->user_nicename . '/' );
			$values[ 'image' ] = preg_replace( '#<img[^>]*src=[\'"](.*?)[\'"][^>]*>#', '$1', get_avatar( $this->doujin->ID, 600 ) );
			$values[ 'desc' ] = $this->doujin->user_description;
		}

		return $values;
	}


	/**
	 * Do response
	 *
	 * Echo JSON with set data.
	 *
	 * @param array $data
	 */
	protected function format( $data ) {
		if ( isset( $data['reviews'] ) ) {
			$path = '/assets/js/dist/admin/profile.js';
			wp_enqueue_script( 'hametha-profile', get_stylesheet_directory_uri() . $path, [
				'jquery',
				'google-jsapi',
			], filemtime( get_stylesheet_directory() . $path ), true );
			wp_localize_script( 'hametha-profile', 'HametuhaReviews', $data['reviews'] );
		} elseif ( isset( $data['template'] ) && 'follower' == $data['template'] ) {
			$path = '/assets/js/dist/components/followers.js';
			wp_enqueue_script( 'hametuha-follower', get_stylesheet_directory_uri() . $path, [
				'angular',
				'hametu-follow',
			], filemtime( get_stylesheet_directory() . $path ), true );
		}
		$this->load_template( 'templates/doujin/base' );
	}
}
