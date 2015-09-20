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
		$errors = [];
		if ( empty( $title ) ) {
			$errors[] = '名前・引用元が入力されていません。';
		}
		if ( empty( $text ) ) {
			$errors[] = 'レビュー本文が入力されていません。';
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
				'comment_author'     => $title,
				'user_id'            => get_current_user_id(),
				'comment_content'    => $text,
				'comment_post_ID'    => $series->ID,
				'comment_type'       => 'review',
				'comment_author_url' => $url,
				'comment_approved'   => current_user_can( 'edit_post', $series->ID ) ? '1' : '0',
				'comment_author_email' => get_userdata(get_current_user_id())->user_email,
				'comment_agent' => $_SERVER['HTTP_USER_AGENT'],
				'comment_author_IP' => $_SERVER['REMOTE_ADDR'],
			] );
			update_comment_meta( $comment_id, 'show_public', '1' );
			update_comment_meta( $comment_id, 'review_rank', $rank );
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
