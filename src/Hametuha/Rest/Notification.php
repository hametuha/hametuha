<?php

namespace Hametuha\Rest;


use Hametuha\Model\Notifications;
use Hametuha\Model\Rating;
use Hametuha\Model\Review;
use WPametu\API\Rest\RestTemplate;

/**
 * Class Notification
 * @package Hametuha\Hametuha\Rest
 * @property-read Notifications $notifications
 * @property-read Review $review
 * @property-read Rating $rating
 */
class Notification extends RestTemplate {

	protected $title = '通知';

	protected $screen = 'public';


	/**
	 * @var string
	 */
	public static $prefix = 'notification';

	protected $action = 'notification';

	protected $models = [
		'notifications' => Notifications::class,
		'review'        => Review::class,
		'rating'        => Rating::class,
	];

	const CRON_ACTION = 'hametuha_daily_notification';

	/**
	 * フックを登録
	 *
	 * @param array $setting
	 */
	protected function __construct( array $setting = [] ) {
		parent::__construct( $setting );
		add_action( 'hametuha_post_reviewed', [ $this, 'review_updated' ], 10, 4 );
		add_action( 'wp_insert_comment', [ $this, 'comment_inserted' ], 11, 2 );
		add_action( 'transition_post_status', [ $this, 'transition_post_status' ], 10, 3 );
		if ( ! wp_next_scheduled( self::CRON_ACTION ) ) {
			$time = date_i18n( 'Y-m-dT11:00:00+09:00', current_time( 'timestamp' ) + 60 * 60 * 24 );
			wp_schedule_event( strtotime( $time ), 'daily', self::CRON_ACTION );
		}
		add_action( self::CRON_ACTION, [ $this, 'daily_notice' ] );
	}

	/**
	 * JSを登録
	 *
	 * @param string $page
	 */
	public function enqueue_assets( $page = '' ) {
		wp_enqueue_script( 'hametuha-notification', get_stylesheet_directory_uri() . '/assets/js/dist/components/notification.js', [ 'jquery' ], filemtime( get_stylesheet_directory() . '/assets/js/dist/components/notification.js' ), true );
		wp_localize_script( 'hametuha-notification', 'HametuhaNotification', [
			'endpoint' => home_url( static::$prefix . "/update" ),
			'retrieve' => home_url( static::$prefix . "/latest" ),
			'nonce'    => wp_create_nonce( $this->action ),
		] );
	}

	/**
	 * Get all notifications
	 *
	 * @param string $page
	 * @param int $paged
	 *
	 * @throws \Exception
	 */
	public function get_all( $page = 'page', $paged = 1 ) {
		$this->get_list( '', $paged );
	}

	/**
	 * Show general works
	 *
	 * @param string $page
	 * @param int $paged
	 */
	public function get_general( $page = 'page', $paged = 1 ) {
		$this->get_list( 'general', $paged );
	}

	/**
	 * Show list of your works
	 *
	 * @param string $page
	 * @param int $paged
	 */
	public function get_works( $page = 'page', $paged = 1 ) {
		$this->get_list( 'works', $paged );
	}

	/**
	 * Get list
	 *
	 * @param string $type
	 * @param int $paged
	 *
	 * @throws \Exception
	 */
	protected function get_list( $type = '', $paged = 1 ) {
		nocache_headers();
		if ( ! is_user_logged_in() ) {
			throw new \Exception( 'ログインしてください。', 403 );
		}
		switch ( $type ) {
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
		$notifications = [];
		foreach ( $this->notifications->get_notifications( $user_ids, '', $paged ) as $notification ) {
			ob_start();
			$this->block( $notification );
			$notifications[] = ob_get_contents();
			ob_end_clean();
		}
		$this->set_data( [
			'notifications' => $notifications,
			'type'          => $type,
		] );
		$this->load_template( 'index-notification', 'お知らせ' );
		exit;
	}

	/**
	 * Update last checked
	 *
	 * @throws \Exception
	 */
	public function post_update() {
		nocache_headers();
		if ( ! is_user_logged_in() && ! $this->verify_nonce() ) {
			throw new \Exception( 'ログインしてください。', 403 );
		}
		wp_send_json( [
			'checked' => $this->notifications->update_login( get_current_user_id() ),
		] );
	}

	/**
	 * 最新のお知らせを返す
	 *
	 * @throws \Exception
	 */
	public function get_latest() {
		nocache_headers();
		if ( ! is_user_logged_in() && ! $this->verify_nonce() ) {
			throw new \Exception( 'ログインしてください。', 403 );
		}
		$user_id = get_current_user_id();
		$notifications = wp_cache_get( $user_id, 'latest_info' );
		if ( false === $notifications ) {
			$notifications = [];
			foreach ( $this->notifications->get_recent( get_current_user_id() ) as $notification ) {
				ob_start();
				$this->block( $notification );
				$content = ob_get_contents();
				ob_end_clean();
				$notifications[] = $content;
			}
			wp_cache_add( $user_id, $notifications, 'latest_info', 30 );
		}
		wp_send_json( $notifications );
	}


	/**
	 * レビューが保存されたとき
	 *
	 * @param \WP_Post $post
	 * @param int $user_id
	 * @param array $reviewed_terms
	 * @param int $rank
	 */
	public function review_updated( \WP_Post $post, $user_id = 0, $reviewed_terms = [], $rank = 0 ) {
		$count = $this->review->get_review_count( $post->ID );
		for ( $i = 4; $i >= 0; $i -- ) {
			$step = pow( 10, $i );
			if ( $step == $count ) {
				$key = '_is_notified_' . $step;
				if ( get_post_meta( $post->ID, $key, true ) ) {
					break;
				}
				$label = sprintf( '%s件', number_format( $count ) );
				switch ( $count ) {
					case 10000:
						$subtitle = '世界はあなたのもの！';
						break;
					case 1000:
						$subtitle = '快挙達成！';
						break;
					case 100:
						$subtitle = 'ついにブレイク！';
						break;
					case 10:
						$subtitle = 'ブレイク間近！？';
						break;
					case 1:
					default:
						$subtitle = 'おめでとうございます！';
						$label    = 'はじめて';
						break;
				}
				$msg = sprintf( '%s<strong>%s</strong>に%sのレビューがつきました！', $subtitle, get_the_title( $post ), $label );
				$this->notifications->add_review( $post->post_author, $post->ID, $msg, $user_id );
				update_post_meta( $post->ID, $key, true );
				break;
			}
		}
	}

	/**
	 * コメントがついたとき
	 *
	 * @param int $comment_id
	 * @param \stdClass $comment_object
	 */
	public function comment_inserted( $comment_id, $comment_object ) {
		if ( ! $comment_object->comment_type && ( $post = get_post( $comment_object->comment_post_ID ) ) && $comment_object->user_id && ( $post->post_author != $comment_object->user_id ) ) {
			// This is comment
			$msg = trim_long_sentence( strip_tags( $comment_object->comment_content ), 80 );
			$this->notifications->add_comment( $post->post_author, $comment_id, sprintf( '<strong>%s</strong>に「%s」というコメントがつきました。', get_the_title( $post ), $msg ), ( $comment_object->user_id ?: $comment_object->comment_author_email ) );
			// If this is reply
			if ( $comment_object->comment_parent // これは返信であり
			     && ( $parent = get_comment( $comment_object->comment_parent ) ) // 親コメントが存在し
			     && $parent->user_id // 親コメントには通知すべきユーザーがおり
			     && $post->post_author != $parent->user_id // 投稿作成者と親コメントのユーザーが一致せず
			     && $parent->user_id != $comment_object->user_id // 投稿者と親コメント者が一緒じゃなければ
			) {
				$this->notifications->add_comment( $parent->user_id, $comment_id, sprintf( 'あなたのコメントに「%s」という返信がつきました。', $msg ), ( $comment_object->user_id ?: $comment_object->comment_author_email ) );
			}
		}
	}

	/**
	 * 投稿が公開されたとき
	 *
	 * @param string $new_status
	 * @param string $old_status
	 * @param \WP_Post $post
	 */
	public function transition_post_status( $new_status, $old_status, \WP_Post $post ) {
		if ( 'publish' === $new_status ) {
			if ( false === array_search( $old_status, [ 'new', 'draft', 'pending', 'auto-draft', 'future' ] ) ) {
				return;
			}
			switch ( $post->post_type ) {
				case 'announcement':
					$message = sprintf( "破滅派からお知らせです: <strong>%s</strong>", get_the_title( $post ) );
					$this->notifications->add_general( 0, $post->ID, $message, 'info@hametuha.com' );
					break;
				default:
					// Do nothing
					break;
			}
		}
	}

	/**
	 * 毎日昼になにかをする
	 */
	public function daily_notice() {
		switch ( date_i18n( 'N' ) ) {
			case '5': // 金曜日（週間ランキング発表）
				$message = '【お知らせ】おめでとうございます。%s付の週間ランキングで%d位になりました。';
				list( $year, $month, $day ) = explode( '/', get_latest_ranking_day( 'Y/m/d' ) );
				$query = new \WP_Query( [
					'ranking'        => 'weekly',
					'year'           => $year,
					'monthnum'       => $month,
					'day'            => $day,
					'posts_per_page' => 3,
				] );
				if ( $query->have_posts() ) {
					$query->the_post();
					$this->notifications->add_general(
						get_the_author_meta( 'ID' ),
						get_the_ID(),
						sprintf( $message, get_latest_ranking_day( get_option( 'date_format' ) ), get_the_ranking() ),
						'info@hametuha.com' );
				}
				break;
			default:
				// なにもしない
				break;

		}
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
		$new     = $this->last_checked() < $time;
		switch ( $notification->type ) {
			case Notifications::TYPE_COMMENT:
				$img = get_avatar( $notification->avatar, 80 );
				break;
			default:
				$img = '';
				break;
		}
		include get_template_directory() . '/parts/loop-notification.php';
	}

	/**
	 * Show recent blocks
	 *
	 * @return bool
	 */
	public function recent_blocks() {
		$notifications = $this->notifications->get_recent( get_current_user_id() );
		if ( ! $notifications ) {
			return false;
		}
		foreach ( $notifications as $n ) {
			$this->block( $n );
		}

		return true;
	}

	/**
	 * Get last checked
	 *
	 * @return int
	 */
	public function last_checked() {
		return $this->notifications->get_last_checked( get_current_user_id() );
	}

}