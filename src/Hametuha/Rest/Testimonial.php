<?php

namespace Hametuha\Rest;


use Hametuha\Model\Series;
use WPametu\API\Rest\RestTemplate;

/**
 * Testimonial Controller
 *
 * @package Hametuha\Rest
 * @property-read Series $series
 */
class Testimonial extends RestTemplate {

	public static $prefix = 'testimonials';

	protected $title = 'レビュー';

	protected $action = 'manage_testimonial';

	protected $content_type = 'text/html';

	protected $template = '';

	protected $models = [
		'series' => Series::class,
	];

	/**
	 * トップページ（現在はなし）
	 *
	 * @param int $page
	 */
	protected function pager( $page = 1 ) {
		$this->method_not_found();
	}

	public function get_add( $id ) {
		nocache_headers();
		$this->check_login();
		$series = $this->check_series( $id );
		$this->set_data( $series, 'post' );
		$this->set_template( 'form-add' );
		$this->response();
	}

	public function post_add( $id ) {
		nocache_headers();
		$this->check_login();
		$series = $this->check_series( $id );
		$this->verify_nonce();
		$title  = $this->input->post( 'testimonial-source' );
		$url    = $this->input->post( 'testimonial-url' );
		$text   = $this->input->post( 'testimonial-text' );
		$rank   = $this->input->post( 'testimonial-rank' );
		$errors = [ ];
		if ( ! $this->series->is_service( $url, 'twitter' ) ) {
			if ( empty( $title ) ) {
				$errors[] = '名前・引用元が入力されていません。';
			}
			if ( empty( $text ) ) {
				$errors[] = 'レビュー本文が入力されていません。';
			}

		}
		if ( false === array_search( $rank, range( 0, 5 ) ) ) {
			$errors[] = '五段階評価の値が不正です。';
		}
		if ( ! empty( $url ) && ! preg_match( '#^https?://#', $url ) ) {
			$errors[] = 'URLの形式が不正です。';
		}
		if ( $errors ) {
			$json = [
				'success' => false,
				'message' => implode( '<br />', $errors ),
			];
		} else {
			$comment_id = wp_insert_comment( [
				'comment_author'       => $title,
				'user_id'              => get_current_user_id(),
				'comment_content'      => $text,
				'comment_post_ID'      => $series->ID,
				'comment_type'         => 'review',
				'comment_author_url'   => $url,
				'comment_approved'     => current_user_can( 'edit_post', $series->ID ) ? '1' : '0',
				'comment_author_email' => get_userdata( get_current_user_id() )->user_email,
				'comment_agent'        => $_SERVER['HTTP_USER_AGENT'],
				'comment_author_IP'    => $_SERVER['REMOTE_ADDR'],
			] );
			update_comment_meta( $comment_id, 'testimonial_rank', $rank );
			$json = [
				'success' => true,
				'message' => current_user_can( 'edit_post', $id )
					? 'レビューが登録されました。'
					: 'ありがとうございました。承認された場合は公開されません。',
			];
		}
		wp_send_json( $json );
	}

	/**
	 * Update list
	 *
	 * @param int $id
	 * @param string $page
	 * @param int $paged
	 *
	 * @throws \Exception
	 */
	public function get_manage( $id, $page = 'page', $paged = 1 ) {
		nocache_headers();
		$this->check_login();
		$series = $this->check_series( $id );
		if ( ! current_user_can( 'edit_post', $series->ID ) ) {
			$this->error( 'あなたには編集権限がありません', 403 );
		}
		$this->set_data( [
			'post'         => $series,
			'testimonials' => $this->series->get_reviews( $series->ID, false, $paged, 10 ),
		] );
		// TODO: Remove body class.
		add_filter( 'body_class', function( $classes ) {
			$classes[] = 'no-modal-background';
			return $classes;
		} );
		$this->set_template( 'list' );
		$this->response();
	}

	public function post_edit( $comment_id ) {
		$this->check_login();
		if ( ! current_user_can( 'edit_comment', $comment_id ) ) {
			throw new \Exception( 'あなたには権限がありません', 403 );
		}
		$comments = get_comments( [ 'comment__in' => [ $comment_id ] ] );
		if ( ! $comments ) {
			throw new \Exception( 'コメントが見つかりません', 404 );
		}
		foreach ( $comments as $comment ) {
			if ( ! current_user_can( 'edit_post', $comment->comment_post_ID ) ) {
				throw new \Exception( 'あなたには権限がありません', 403 );
			}
			$is_twitter = $this->series->is_service( $comment->comment_author_url, 'twitter' );
			$is_parent  = 'series' === get_post_type( $comment->comment_post_ID );
			// Common process
			$priority = (int) $this->input->post( 'comment-priority' );
			update_comment_meta( $comment_id, 'testimonial_order', $priority );
			// Rating
			if ( $is_parent && ! $is_twitter ) {
				// Update rating
				update_comment_meta( $comment_id, 'testimonial_rank', (int) $this->input->post( 'comment-rank' ) );
			}
			$status = (bool) $this->input->post( 'comment-status' );
			if ( 'review' === $comment->comment_type ) {
				$comment_arr = [
					'comment_ID'         => $comment_id,
					'comment_approved'   => (int) $status,
					'comment_author_url' => $this->input->post( 'comment-url' ),
				];
				if ( ! $is_twitter ) {
					$comment_arr['comment_author']  = $this->input->post( 'comment-author' );
					$comment_arr['comment_content'] = $this->input->post( 'comment-content' );
				}
				if ( ! wp_update_comment( $comment_arr ) ) {
					throw new \Exception( 'コメントを更新できませんでした。', 500 );
				}
			} else {
				// This is child's comment
				update_comment_meta( $comment_id, 'as_testimonial', $status );
				$excerpt = $this->input->post( 'comment-excerpt' );
				if ( $excerpt ) {
					foreach ( explode( "\r\n", $excerpt ) as $line ) {
						$line = trim($line);
						if( empty($line) ){
							continue;
						}
						if ( false === strpos( $comment->comment_content, $line ) ) {
							throw new \Exception( 'コメントに含まれる文字列以外は登録できません。', 500 );
						}
					}
				}
				update_comment_meta( $comment_id, 'comment_excerpt', $excerpt );
			}
			$post      = get_post( $comment->comment_post_ID );
			$series_id = $is_parent ? $post->ID : $post->post_parent;
			wp_redirect( home_url( '/testimonials/manage/' . $series_id . '/', 'https' ) );
			exit;
		}
	}

	/**
	 * シリーズをチェック
	 *
	 * @param int $id
	 *
	 * @return array|null|\WP_Post
	 * @throws \Exception
	 */
	protected function check_series( $id ) {
		$series = get_post( $id );
		if ( ! $series || 'series' !== $series->post_type ) {
			throw new \Exception( '指定された投稿は存在しません。', 404 );
		}

		return $series;
	}

	protected function set_template( $template ) {
		$this->template = 'templates/testimonial/' . $template;
	}


	public function format( $data ) {
		$this->load_template( $this->template );
	}

}
