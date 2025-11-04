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
						case 'authors':
							return true;
						default:
							return current_user_can( 'list_users' );
					}
				},
			],
		] );
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
		header(  'Content-Type: text/html;charset=UTF-8' );
		$this->set_data( [
			'breadcrumb' => false,
			'current'    => false,
			'template'   => '',
			'reviews'    => $this->get_review_json(),
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
		}
		$this->load_template( 'templates/doujin/base' );
	}
}
