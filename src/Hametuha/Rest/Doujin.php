<?php

namespace Hametuha\Rest;


use Hametuha\Interfaces\OgpCustomizer;
use Hametuha\Model\Author;
use Hametuha\Model\Review;
use Hametuha\Model\Series;
use WPametu\API\Rest\RestTemplate;

/**
 * Class Profile
 * @package Hametuha\Rest
 * @property-read Series $series
 * @property-read Author $author
 * @property-read Review $review
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
		'series' => Series::class,
		'author' => Author::class,
		'review' => Review::class,
	];

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
		}
		$this->load_template( 'templates/doujin/base' );
	}


}
