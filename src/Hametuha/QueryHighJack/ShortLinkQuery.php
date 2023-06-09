<?php

namespace Hametuha\QueryHighJack;


use Hametuha\Model\ShortLinks;
use WPametu\API\QueryHighJack;

/**
 * リダイレクト
 *
 * @package Hametuha\Hametuha\QueryHighJack
 * @property-read ShortLinks $links
 */
class ShortLinkQuery extends QueryHighJack {


	/**
	 * Query vars
	 *
	 * @var array
	 */
	protected $query_var = [ 'short_link' ];

	protected $models = [
		'links' => ShortLinks::class,
	];

	/**
	 * リライトルール
	 *
	 * @var array
	 */
	protected $rewrites = [
		'^l/([^/]+)/?$' => 'index.php?short_link=$matches[1]',
	];

	/**
	 * Make short URL
	 *
	 * @param \WP_Query $wp_query
	 */
	public function pre_get_posts( \WP_Query &$wp_query ) {
		if ( $this->is_valid_query( $wp_query ) ) {
			$original = $this->links->get_original( $wp_query->get( 'short_link' ) );
			if ( $original ) {
				wp_redirect( $original, 301 );
				exit;
			} else {
				$wp_query->set_404();
			}
		}
	}


	/**
	 * Detect if query var is valid
	 *
	 * @param \WP_Query $wp_query
	 *
	 * @return bool
	 */
	protected function is_valid_query( \WP_Query $wp_query ) {
		return (bool) $wp_query->get( 'short_link' );
	}


}
