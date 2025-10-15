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

	protected $title = '推薦文';

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

	public function wp_title( $title, $sep, $location ) {
		return implode( " {$sep} ",  [ 'レビュー管理', '破滅派' ] );
	}

	public function get_add( $id ) {
		nocache_headers();
		$this->check_login();
		$this->content_type = 'text/html';
		$series = $this->check_series( $id );
		$this->set_data( $series, 'post' );
		$this->set_template( 'form-add' );
		$this->response();
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
		$this->set_template( 'list' );
		$this->response();
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
