<?php

namespace Hametuha\Rest;


use Hametuha\Model\Jobs;
use WPametu\API\Rest\RestTemplate;

/**
 * Image Generator
 *
 * @package Hametuha\Rest
 * @property-read Jobs $jobs
 */
class ImageGen extends RestTemplate {


	public static $prefix = 'image-gen';

	protected $title = '';

	/**
	 * モデル
	 *
	 * @var array
	 */
	protected $models = [
		'jobs' => Jobs::class,
	];

	/**
	 * フォーム表示
	 *
	 * @param int $page
	 */
	public function pager( $page = 1 ) {
		$this->method_not_found();
	}

	/**
	 * Get quote
	 */
	public function get_quote( $job_id ) {
		$job = $this->jobs->get( $job_id );
		if ( ! $job || 'text_to_image' != $job->job_key ) {
			throw new \Exception( '該当するクォートは存在しません。', 500 );
		}

		$post = get_post( $job->meta['post_id'] );
		if ( ! $post || 'publish' != $post->post_status ) {
			throw new \Exception( '該当する投稿は存在しません。', 500 );
		}
		$this->title = get_the_title( $post );
		$this->set_data(
			[
				'post' => $post,
				'text' => $job->meta['text'],
				'user' => get_userdata( $job->issuer_id ),
			]
		);
		add_filter(
			'body_class',
			function( $classes ) {
				$classes[] = 'quote';
				return $classes;
			}
		);
		$this->load_template( 'templates/quote' );
	}

	/**
	 * Get tokenized string
	 *
	 * @param string $string
	 *
	 * @return array|\WP_Error
	 */
	protected function tokenize( $string ) {
		$endpoint = 'https://punctuate.space/json?q=' . rawurlencode( $string );
		$response = wp_remote_get( $endpoint );
		if ( is_wp_error( $response ) ) {
			return $response;
		}
		$tokens = [];
		$i      = 0;
		$store  = '';
		foreach ( json_decode( $response['body'] ) as $token ) {
			// If store exists, prepend it.
			if ( $store ) {
				$token = $store . $token;
				$store = '';
			}
			// If end with start parentheses, store it.
			if ( preg_match( '#^(.*)([『「（])$#u', $token, $matches ) ) {
				$token = $matches[1];
				$store = $matches[2];
			}
			$tokens[ $i ] = $token;
			$i ++;
		}
		return $tokens;
	}
}
