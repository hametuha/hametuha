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
}
