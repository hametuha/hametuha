<?php
/**
 * リライトルール全般
 */

/**
 * 投稿取得前に必ず呼び出す
 * @param WP_Query $wp_query 
 */
function _hametuha_pre_get_posts($wp_query){
	if($wp_query->is_main_query()){
		if((is_page('authors') || is_page('hamazon')) && isset($_GET['s'])){
			add_filter('posts_where', '_hametuha_user_search_filter');
		}
	}
}
add_action('pre_get_posts', '_hametuha_pre_get_posts');


/**
 * ユーザー検索ページの場合、where節を変える
 * @global wpdb $wpdb
 * @param string $where
 * @return string 
 */
function _hametuha_user_search_filter($where){
	global $wpdb;
	remove_filter('posts_where', '_hametuha_user_search_filter');
	$query_to_remove = "/\(\({$wpdb->posts}.post_title LIKE '%.*?%'\) OR \({$wpdb->posts}.post_content LIKE '%.*?%'\)\)/";
	$where = preg_replace($query_to_remove, "(1 = 1)", $where);
	return $where;
}