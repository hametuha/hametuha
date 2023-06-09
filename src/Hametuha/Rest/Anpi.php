<?php

namespace Hametuha\Rest;


use Hametuha\Model\Anpis;
use Hametuha\Model\Notifications;
use WPametu\API\Rest\RestTemplate;

/**
 * Anpi Controller
 *
 * @package Hametuha\Rest
 * @property-read Anpis $anpis
 * @property-read Notifications $notifications
 */
class Anpi extends RestTemplate {


	public static $prefix = 'anpi/mine';

	protected $title = '私の安否情報';

	protected $screen = 'public';

	protected $action = 'my-anpi';

	protected $content_type = 'text/html';

	protected $models = [
		'anpis'         => Anpis::class,
		'notifications' => Notifications::class,
	];

	protected $screen_name = false;

	/**
	 * RestBase constructor.
	 *
	 * @param array $setting Setting value.
	 */
	public function __construct( array $setting ) {
		parent::__construct( $setting );
		add_action( 'the_posts', [ $this, 'the_posts' ], 10, 2 );
	}

	/**
	 * Override this if you need rest API
	 */
	public function rest_api_init() {
		register_rest_route( 'hametuha/v1', '/anpi/(?P<post_id>\\d|new)/?', [
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'api_create' ],
				'permission_callback' => function () {
					return current_user_can( 'read' );
				},
				'args'                => [
					'post_id' => [
						'validate_callback' => function( $var ) {
							return 'new' === $var;
						},
						'required'          => true,
					],
					'content' => [
						'validate_callback' => function( $var ) {
							return ! empty( $var );
						},
						'required'          => true,
					],
					'mention' => [
						'default'           => '',
						'sanitize_callback' => function( $mention ) {
							$mention = (string) $mention;
							return array_filter( array_map( 'intval', explode( ',', $mention ) ), function( $var ) {
								return $var;
							});
						},
					],
				],
			],
		]);
	}

	/**
	 * Create endpoint
	 *
	 * @param array $params
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function api_create( $params ) {
		$post_id = $this->anpis->create_tweet( get_current_user_id(), $params['content'], $params['mention'] );
		if ( is_wp_error( $post_id ) ) {
			return new \WP_Error( $post_id->get_error_code(), $post_id->get_error_message(), [ 'status' => 500 ] );
		} else {
			if ( $params['mention'] ) {
				$user    = get_userdata( get_current_user_id() );
				$message = "{$user->display_name}さんがあなたに安否を報告しています。";
				foreach ( $params['mention'] as $user_id ) {
					$this->notifications->add_notification( 'mention', $user_id, $post_id, $message, get_current_user_id() );
				}
			}
			return new \WP_REST_Response( [
				'success' => true,
				'message' => '安否報告が完了しました。',
			] );
		}
	}

	/**
	 * Pager
	 *
	 * @param int $page Page number.
	 * @throws \Exception
	 */
	protected function pager( $page = 1 ) {
		$this->wp_query->set( 'post_type', 'anpi' );
		$this->wp_query->set( 'author', get_current_user_id() );
		$this->wp_query->set( 'paged', $page );
		throw new \Exception( 'This is your anpi archive page.', 200 );
	}

	/**
	 * 安否情報を作成する
	 */
	public function get_new() {
		nocache_headers();
		if ( ! current_user_can( 'edit_posts' ) || ! $this->verify_nonce() ) {
			wp_die( 'あなたにはこのページにアクセスする権限がありません。', get_status_header_desc( 403 ), [
				'back_link' => true,
				'response'  => 403,
			] );
		}
		$post = null;
		foreach (
			get_posts( [
				'post_type'      => 'anpi',
				'author'         => get_current_user_id(),
				'post_status'    => 'auto-draft',
				'posts_per_page' => 1,
				'orderby'        => [ 'post_modified' => 'ASC' ],
			] ) as $p
		) {
			$post = $p;
		}
		if ( ! $post ) {
			$post_id = $this->anpis->create_base_anpi( get_current_user_id() );
			if ( is_wp_error( $post_id ) ) {
				wp_die( $post_id->get_error_message(), get_status_header_desc( 500 ), [
					'response'  => 500,
					'back_link' => true,
				] );
			} else {
				$post = get_post( $post_id );
			}
		}
		wp_redirect( home_url( "/anpi/mine/edit/{$post->ID}/", 'https' ) );
		exit;
	}


	/**
	 * Show edit screen
	 *
	 * @param int $post_id
	 */
	public function get_edit( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post || 'anpi' !== $post->post_type || $this->anpis->is_tweet( $post ) ) {
			wp_die('該当する安否情報は存在しません。', get_status_header_desc( 404 ), [
				'back_link' => true,
				'response'  => 404,
			]);
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die('あなたにはこの投稿の編集権限がありません。', get_status_header_desc( 401 ), [
				'back_link' => true,
				'response'  => 401,
			]);
		}
		if ( 'trash' === $post->post_status ) {
			wp_die( 'この投稿はゴミ箱に入っています。', get_status_header_desc( 403 ), [
				'back_link' => true,
				'response'  => 403,
			]);
		}
		$this->title       = '安否情報編集';
		$this->screen_name = 'editor';
		nocache_headers();
		$this->set_data( [
			'post' => $post,
		] );
		$terms = [];
		foreach ( get_terms( 'anpi_cat', [ 'hide_empty' => false ] ) as $term ) {
			$terms[] = [
				'id'     => $term->term_id,
				'name'   => $term->name,
				'active' => has_term( $term->term_id, 'anpi_cat', $post ),
			];
		}
		wp_localize_script('hameditor', 'HameditorPost', [
			'id'         => $post->ID,
			'type'       => 'anpi',
			'status'     => $post->post_status,
			'title'      => $post->post_title,
			'url'        => get_permalink( $post ),
			'date'       => get_gmt_from_date( $post->post_date, DATE_ISO8601 ),
			'modified'   => get_gmt_from_date( $post->post_modified, DATE_ISO8601 ),
			'categories' => $terms,
			'content'    => $post->post_content,
		]);
		$this->load_template( 'templates/editor/anpi', '' );
	}

	/**
	 * Enqueue scripts and asset
	 *
	 * @param string $page Available only on admin screen
	 */
	public function enqueue_assets( $page = '' ) {
		if ( 'editor' === $this->screen_name ) {
			add_filter( 'editor_stylesheets', function( $css ) {
				$css[] = get_template_directory_uri() . '/assets/css/editor-style.css';
				return $css;
			} );
			wp_enqueue_script( 'anpi-editor', get_template_directory_uri() . '/assets/js/dist/editor/anpi.js', [ 'hameditor' ], hametuha_version(), true );
		}
	}


	/**
	 * Get post form
	 */
	public function post_new() {
		try {
			if ( ! current_user_can( 'read' ) ) {
				throw new \Exception( 'ログインしている必要があります。', 401 );
			}
			$response = [
				'success' => true,
				'html'    => hameplate( 'templates/anpi/form', '', [
					'id'      => 0,
					'content' => '',
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
	 * Add mention to posts
	 *
	 * @param array $posts
	 * @param \WP_Query $wp_query
	 *
	 * @return mixed
	 */
	public function the_posts( $posts, \WP_Query $wp_query ) {
		if ( 'anpi' == $wp_query->get( 'post_type' ) ) {
			$ids = [];
			foreach ( $posts as $post ) {
				if ( 'anpi' == $post->post_type ) {
					$ids[] = $post->ID;
				}
			}
			$users = $this->anpis->get_mentioned( $ids );
			foreach ( $posts as &$post ) {
				if ( 'anpi' == $post->post_type && isset( $users[ $post->ID ] ) ) {
					$post->mention_to = $users[ $post->ID ];
				} else {
					$post->mention_to = [];
				}
			}
		}

		return $posts;
	}
}
