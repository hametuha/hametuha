<?php

namespace Hametuha\QueryHighJack;


use Hametuha\Model\Review;
use WPametu\API\QueryHighJack;

/**
 * Review Query override
 *
 * @package Hametuha\Rest
 * @property-read Review $review
 */
class ReviewQuery extends QueryHighJack {


	/**
	 * クエリバー
	 *
	 * @var array
	 */
	protected $query_var = [ 'reviewer' ];

	/**
	 * リライトルール
	 *
	 * @var array
	 */
	protected $rewrites = [
		'your/reviews/?'               => 'index.php?reviewer=0',
		'your/reviews/page/([0-9]+)/?' => 'index.php?reviewer=0&paged=$matches[1]',
	];

	/**
	 * Model classes
	 *
	 * @var array
	 */
	protected $models = [
		'review' => Review::class,
	];

	/**
	 * タイトルの上書き
	 *
	 * @param string $title
	 * @param string $sep
	 * @param string $sep_location
	 *
	 * @return string
	 */
	public function wp_title( $title, $sep, $sep_location ) {
		return "レビューした作品 {$sep} " . get_bloginfo( 'name' );
	}

	/**
	 * レビュアーのIDを設定する
	 *
	 * @param \WP_Query $wp_query
	 */
	public function pre_get_posts( \WP_Query &$wp_query ) {
		if ( $this->is_valid_query( $wp_query ) ) {
			if ( $wp_query->is_main_query() ) {
				nocache_headers();
			}
			$reviewer_id = (int) $wp_query->get( 'reviewer' );
			if ( ! $reviewer_id ) {
				if ( is_user_logged_in() ) {
					$wp_query->set( 'reviewer', get_current_user_id() );
				} else {
					$wp_query->set( 'reviewer', '' );
					$wp_query->set_404();
				}
			} else {
				// IDが指定されている。現在のログインIDと異なったら非表示
				if ( $reviewer_id != get_current_user_id() ) {
					$wp_query->set( 'reviewer', '' );
					$wp_query->set_404();
				}
			}
		}
	}

	/**
	 * Distinct
	 *
	 * @param string $distinct
	 * @param \WP_Query $wp_query
	 * @return string
	 */
	public function posts_distinct( $distinct, \WP_Query $wp_query ) {
		if ( $this->is_valid_query( $wp_query ) ) {
			$distinct = ' DISTINCT ';
		}
		return $distinct;
	}

	/**
	 * Add join clause
	 *
	 * @param string $join
	 * @param \WP_Query $wp_query
	 * @return mixed|string
	 */
	public function posts_join( $join, \WP_Query $wp_query ) {
		if ( $this->is_valid_query( $wp_query ) ) {
			$join = $this->review->reviewed_join( $join );
		}
		return $join;
	}

	/**
	 * Add where clause
	 *
	 * @param string $where
	 * @param \WP_Query $wp_query
	 * @return string
	 */
	public function posts_where( $where, \WP_Query $wp_query ) {
		if ( $this->is_valid_query( $wp_query ) ) {
			$where = $this->review->reviewed_where( $where, $wp_query->get( 'reviewer' ) );
		}
		return $where;
	}

	/**
	 * Detect if query var is valid
	 *
	 * @param \WP_Query $wp_query
	 * @return bool
	 */
	protected function is_valid_query( \WP_Query $wp_query ) {
		return is_numeric( $wp_query->get( 'reviewer' ) );
	}
}
