<?php

namespace Hametuha\QueryHighJack;


use Hametuha\Model\Series;
use WPametu\API\QueryHighJack;

/**
 * Review results Query
 *
 * @package Hametuha\Rest
 * @property-read Series $series
 */
class KdpQuery extends QueryHighJack {


	/**
	 * @var string
	 */
	protected $pseudo_post_type = 'profile';

	/**
	 * Query vars
	 *
	 * @var array
	 */
	protected $query_var = [ 'meta_filter' ];

	protected $models = [
		'series' => Series::class,
	];

	/**
	 * リライトルール
	 *
	 * @var array
	 */
	protected $rewrites = [
		'kdp/page/([0-9]+)/?$' => 'index.php?post_type=series&meta_filter=kdp&paged=$matches[1]',
		'kdp/?$'               => 'index.php?post_type=series&meta_filter=kdp',
	];

	/**
	 * タイトル変更
	 *
	 * @param string $title
	 * @param string $sep
	 * @param string $sep_location
	 *
	 * @return string
	 */
	public function wp_title( $title, $sep, $sep_location ) {
		return '破滅派の電子書籍';
	}

	/**
	 * action for pre_get_posts
	 *
	 * @param \WP_Query $wp_query
	 */
	public function pre_get_posts( \WP_Query &$wp_query ) {
		if ( $this->is_valid_query( $wp_query ) ) {
			$this->add_meta_query( $wp_query, [
				'key'     => '_kdp_status',
				'value'   => [ 2 ],
				'compare' => 'IN',
			] );
			$wp_query->set( 'orderby', 'menu_order' );
			$wp_query->set( 'order', 'DESC' );
			$wp_query->set( 'posts_per_page', 24 );
			// テンプレートを変更する
			add_filter( 'template_include', [ $this, 'template_include' ] );
		}
	}


	/**
	 * クエリがKindleかどうか
	 *
	 * @param \WP_Query $wp_query
	 *
	 * @return bool
	 */
	protected function is_valid_query( \WP_Query $wp_query ) {
		return 'kdp' === $wp_query->get( 'meta_filter' );
	}

	/**
	 * テンプレートを変更
	 *
	 * @param string $template Template path.
	 * @return string
	 */
	public function template_include( $template ) {
		return get_template_directory() . '/archive-kdp.php';
	}
}
