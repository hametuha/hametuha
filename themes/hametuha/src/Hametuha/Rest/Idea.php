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
				'html'    => hameplate( 'templates/form', 'idea', [
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
			$idea     = get_post( $post_id );
			$response = [
				'success' => true,
				'html'    => hameplate( 'templates/form', 'idea', [
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
		if ( in_array( $wp_query->get( 'post_type' ), [ '', 'any', 'ideas' ], true ) ) {
			$posts_to_retrieve = [];
			foreach ( $posts as $post ) {
				if ( 'ideas' == $post->post_type ) {
					$posts_to_retrieve[] = $post->ID;
					$post->stock         = 0; // Default value
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
