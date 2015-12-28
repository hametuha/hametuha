<?php

namespace Hametuha\Rest;


use Hametuha\Interfaces\OgpCustomizer;
use Hametuha\Model\Author;
use Hametuha\Model\Follower;
use Hametuha\Model\Notifications;
use Hametuha\Model\Review;
use Hametuha\Model\Series;
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
	protected $doujin = null;

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
					'validate_callback' => 'is_numeric',
					'default'           => 0,
				],
				's' => [
					'default'           => '',
				],
			],
			'permission_callback' => function () {
				return current_user_can( 'read' );
			},
		] );
		register_rest_route( 'hametuha/v1', '/doujin/following/(?P<id>\\d+|me)/?', [
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
					'validate_callback' => 'is_numeric',
					'default'           => 0,
				],
				's' => [
					'default'           => '',
				],
			],
			'permission_callback' => function () {
				return current_user_can( 'read' );
			},
		] );
		// Follow/Unfollow.
		register_rest_route( 'hametuha/v1', '/doujin/follow/(?P<id>\d+)/?', [
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'add_follower' ],
				'args'                => [
					'id' => [
						'required'          => true,
						'validate_callback' => 'is_numeric',
					],
				],
				'permission_callback' => function () {
					return current_user_can( 'read' );
				},
			],
			[
				'methods'             => 'DELETE',
				'callback'            => [ $this, 'remove_follower' ],
				'args'                => [
					'id' => [
						'required'          => true,
						'validate_callback' => 'is_numeric',
					],
				],
				'permission_callback' => function () {
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
	public function add_follower( $request ) {
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
	public function remove_follower( $request ) {
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
	 * ポータルページ
	 *
	 * @param string $author
	 */
	protected function pager( $author = '' ) {
		// Bypass
		$this->method_not_found();
	}

	public function get_detail( $author_name ) {
		$this->set_member( $author_name );
		$this->title = $this->doujin->display_name . ' | ' . $this->title;
		$this->set_data( [
			'breadcrumb' => false,
			'current'    => false,
			'template'   => '',
			'reviews'    => $this->get_review_json(),
		] );
		$this->response();
	}

	public function get_follower() {
		$this->auth_redirect();
		nocache_headers();
		$this->doujin = new \WP_User( get_current_user_id() );
		$this->title  = 'フォロワー | ' . $this->title;
		$this->set_data( [
			'breadcrumb' => false,
			'current'    => false,
			'template'   => 'follower',
		] );
		$this->response();
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
			throw new \Exception( 'Page Not Found.', 404 );
		}
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
		$values['url']   = home_url( '/doujin/detail/' . $this->doujin->user_nicename . '/', 'http' );
		$values['image'] = preg_replace( '#<img[^>]*src=[\'"](.*?)[\'"][^>]*>#', '$1', get_avatar( $this->doujin->ID, 600 ) );
		$values['desc']  = $this->doujin->user_description;

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
				'angular-bootstrap',
				'hametu-follow',
			], filemtime( get_stylesheet_directory() . $path ), true );
		}
		$this->load_template( 'templates/doujin/base' );
	}


}
