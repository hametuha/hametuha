<?php

namespace Hametuha\QueryHighJack;


use WPametu\API\QueryHighJack;


/**
 * 自分のクエリを表示する
 *
 * @package Hametuha\QueryHighJack
 */
class ListQuery extends QueryHighJack
{

	/**
	 * @var array
	 */
	protected $query_var = ['my-content'];

	/**
	 * リライトルール
	 *
	 * @var array
	 */
	protected $rewrites = [
		'^your/lists/?$' => 'index.php?my-content=lists&post_type=lists&post_status=publish,private&post_author=0',
		'^your/lists/paged/([0-9]+)/?$' => 'index.php?my-content=lists&post_type=lists&post_status=publish,private&paged=$matches[1]&post_author=0',
	];

	/**
	 *
	 * Detect if query var is valid
	 *
	 * @param \WP_Query $wp_query
	 *
	 * @return bool
	 */
	protected function is_valid_query( \WP_Query $wp_query ) {
		return is_user_logged_in() && 'lists' == $wp_query->get('my-content');
	}


	/**
	 * ユーザーIDを指定する
	 *
	 * @param \WP_Query $wp_query
	 */
	public function pre_get_posts( \WP_Query &$wp_query ){
		if( $wp_query->is_main_query() && $this->is_valid_query($wp_query) ){
			// キャッシュさせない
			nocache_headers();
			// クエリにユーザーIDを追加
			$wp_query->set('post_author', get_current_user_id());
		}
	}

} 