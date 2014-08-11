<?php

/**
 * 破滅派で紹介された書籍を取得する
 * @global int $paged
 * @global wpdb $wpdb
 * @global WP_Query $wp_query 
 * @return array
 */
function get_hamazon_posts(){
	global $paged;
	global $wpdb, $wp_query;
	$per_page = get_option('posts_per_page');
	$paged = max(1, absint($wp_query->query_vars['paged']));
	$paged -= 1;
	$offset = $paged * $per_page;
	$sql = <<<EOS
		SELECT SQL_CALC_FOUND_ROWS ID, post_date, post_title, post_type,  post_content, post_author
		FROM {$wpdb->posts}
		WHERE post_status = 'publish' AND post_content LIKE '%[tmkm-amazon]%'
		ORDER BY post_date desc
		LIMIT {$offset}, {$per_page}
EOS;
	return $wpdb->get_results($sql);
}

function search_hamazon(){
	
}

/**
 * 投稿本文からamazonのリストだけ出力する
 * @global WP_Hamazon_List $hamazon_list
 * @param string $post_content 
 */
function echo_hamazon($post_content){
	global $hamazon_list;
	$match = array();
	preg_match_all("/\[tmkm-amazon\](\w+)\[\/tmkm-amazon\]/", $post_content, $match);
	if(!empty($match[1])){
		foreach($match[1] as $asin){
			echo $hamazon_list->format_amazon($asin);
		}
	}
}