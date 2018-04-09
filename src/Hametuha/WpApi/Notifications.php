<?php

namespace Hametuha\WpApi;


use WPametu\API\Rest\WpApi;
use Hametuha\Model\Notifications as NotificationsModel;
use Hametuha\Model\Review;
use Hametuha\Model\Rating;

/**
 * Notification class
 *
 * @package Hametuha\WpApi
 * @property NotificationsModel $notifications
 */
class Notifications extends WpApi {

	protected $models = [
		'notifications' => NotificationsModel::class,
		'review'        => Review::class,
		'rating'        => Rating::class,
	];

	protected function get_route() {
		return 'notifications/(?P<type>[^/]+)';
	}

	/**
	 * Get arguments for method.
	 *
	 * @param string $method 'GET', 'POST', 'PUSH', 'PATCH', 'DELETE', 'HEAD', 'OPTION'
	 *
	 * @return array
	 */
	protected function get_arguments( $method ) {
		switch ( $method ) {
			case 'GET':
				return [
					'type' => [
						'type'     => 'string',
						'description' => '取得すべき通知のタイプ。',
						'required' => true,
						'enum'     => [
							'general', 'works', 'all'
						],
					],
					'paged' => [
						'type' => 'integer',
						'description' => sprintf( '1ページあたり%d件が表示されます。整数にキャストされます。', NotificationsModel::PER_PAGE ),
						'default' => 1,
						'sanitize_callback' => function( $num ) {
							return max( 1, (int) $num );
						},

					],
				];
				break;
			default:
				return [];
		}
	}

	/**
	 * Get notifications.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function handle_get( \WP_REST_Request $request ) {
		switch ( $request->get_param( 'type' ) ) {
			case 'general':
				$user_ids = [ 0 ];
				break;
			case 'works':
				$user_ids = [ get_current_user_id() ];
				break;
			default:
				$user_ids = [ 0, get_current_user_id() ];
				break;
		}
		$notifications = $this->notifications->get_notifications( $user_ids, '', $request->get_param( 'paged' ) );
		$total = $this->notifications->found_count();
		$notifications = array_map( function( $notification ) {
			ob_start();
			$this->block( $notification );
			$block = ob_get_contents();
			ob_end_clean();
			$notification->rendered = $block;
			return $notification;
		}, $notifications );
		$response = new \WP_REST_Response( $notifications );
		nocache_headers();
		$response->set_headers( [
			'X-WP-Total' => $total,
			'X-WP-TotalPages' => ceil( $total / NotificationsModel::PER_PAGE ),
			'X-WP-PerPage' => NotificationsModel::PER_PAGE,
		] );
		return $response;
	}

	/**
	 * Permission check
	 *
	 * @param \WP_REST_Request $request
	 * @return bool
	 */
	public function permission_callback( $request ) {
		return current_user_can( 'read' );
	}

	/**
	 * Get notification block
	 *
	 * @param \stdClass $notification
	 * @param string $size
	 */
	protected function block( \stdClass $notification, $size = 'thumbnail' ) {
		$url     = $this->notifications->build_url( $notification->type, $notification->object_id );
		$message = wp_kses( $notification->message, [ 'strong' => [] ] );
		$time    = strtotime( get_gmt_from_date( 'Y-m-d H:i:s', $notification->created ) );
		$new     = $this->notifications->get_last_checked( get_current_user_id() ) < $time;
		switch ( $notification->type ) {
			case NotificationsModel::TYPE_COMMENT:
				$img = get_avatar( $notification->avatar, 80 );
				break;
			default:
				$img = '';
				break;
		}
		include get_template_directory() . '/parts/loop-notification.php';
	}
}
