<?php

/**
 * emptyのエイリアス。関数を渡すことができる
 * @param mixed $var
 * @return boolean 
 */
function emp($var){
	return empty($var);
}





/**
 * 最近の日時かどうか
 *
 * @param string $datetime
 * @param int $limit
 * @return bool
 */
function is_recent_date($datetime, $limit = 3){
    if( !is_numeric($datetime) ){
        $datetime = strtotime($datetime);
    }
    $limit = current_time('timestamp') - $limit * 60 * 60 * 24;
    return $limit < $datetime;
}



/**
 * どのぐらい前なのかを書式化する
 *
 * @param string|int $datetime MySQLのDatetimeかタイムスタンプ
 * @param bool $timestamp タイムスタンプの場合はtrue
 * @return string
 */
function hametuha_passed_time($datetime, $timestamp = false){
    if( !$timestamp ){
        $datetime = strtotime($datetime);
    }
    $diff = current_time('timestamp') - $datetime;
    if( 60 * 60 > $diff ){
        $unit = '分';
        $divide = round($diff / 60);
    }elseif( 60 * 60 * 24 > $diff ){
        $unit = '時間';
        $divide = round($diff / 60 / 60);
    }elseif( 60 * 60 * 24 * 31 > $diff){
        $unit = '日';
        $divide = round($diff / 60 / 60 / 24);
    }elseif( 60 * 60 * 24 * 365 > $diff ){
        $unit = 'ヶ月';
        $divide = min(12, round($diff / 60 / 60 / 24 / 30.5));
    }else{
        $unit = '年';
        $divide = round($diff / 60 / 60 / 24 / 365);
    }
    return sprintf('%s%s前', number_format($divide), $unit);
}

/**
 * HTML5に対応したtype属性を出力する
 * @global boolean $is_IE
 * @param boolean $echo
 * @return string
 */
function attr_email($echo = true){
	global $is_IE;
	$type = $is_IE ? 'text' : 'email';
	if($echo){
		echo $type;
	}
	return $type;
}

/**
 * HTML5に対応したtype属性を返す
 * @global boolean $is_IE
 * @param boolean $echo
 * @return string
 */
function attr_search($echo = true){
	$type = !is_smartphone() ? 'text' : 'search';
	if($echo){
		echo $type;
	}
	return $type;
}


/**
 * 最後のページか否か
 *
 * @return bool
 */
function is_last_page(){
    global $page, $numpages, $multipage;
    return !$multipage || ($page == $numpages);
}

/**
 *
 * @global WP_Query $wp_query
 * @global wpdb $wpdb
 * @return int
 */
function loop_count(){
	if(is_singular('series')){
		global $wpdb;
		return (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_type = 'post' AND post_parent = %d AND post_status = 'publish'", get_the_ID()));
	}else{
		global $wp_query;
		return (int)$wp_query->found_posts;
	}
}

/**
 * アドセンス広告を出力する
 *
 * @param type $type 
 */
function google_ads($type = 'default'){
	switch($type){
		case 'header':
		case 'narrow':
			?>
			<iframe src="http://rcm-fe.amazon-adsystem.com/e/cm?t=hametuha-22&o=9&p=13&l=ur1&category=special_deal&f=ifr" width="468" height="60" scrolling="no" border="0" marginwidth="0" style="border:none;" frameborder="0"></iframe>
			<?php
			break;
		default:
			?>
			<iframe src="http://rcm-fe.amazon-adsystem.com/e/cm?t=hametuha-22&o=9&p=13&l=ur1&category=books&f=ifr" width="468" height="60" scrolling="no" border="0" marginwidth="0" style="border:none;" frameborder="0"></iframe>
			<?php
			break;
	}
}


/**
 * 現在登録されている作品の数を返す
 *
 * @global wpdb $wpdb 
 * @return int
 */
function get_current_post_count(){
	global $wpdb;
	$sql = <<<EOS
		SELECT COUNT(ID) FROM {$wpdb->posts}
		WHERE post_status = 'publish' AND post_type = 'post'
EOS;
	return (int)$wpdb->get_var($sql);
}

/**
 * 投稿の長さを返す
 *
 * @global wpdb $wpdb
 * @param mixed $post
 * @return int 
 */
function get_post_length($post = null){
	global $wpdb;
	$post = get_post($post);
	if($post->post_type == 'series'){
		$sql = <<<EOS
			SELECT SUM(CHAR_LENGTH(post_content)) FROM {$wpdb->posts}
			WHERE post_type = 'post' AND post_status = 'publish' AND post_parent = %d
EOS;
		return intval($wpdb->get_var($wpdb->prepare($sql, $post->ID)));
	}else{
		return mb_strlen(strip_tags(apply_filters('the_content', $post->post_content)));
	}
}

/**
 * 投稿の長さを出力する
 *
 * @param string $prefix
 * @param string $suffix
 * @param string $placeholder
 * @param int $per_page
 * @param mixed $post 
 */
function the_post_length($prefix = '', $suffix = '', $placeholder = '0', $per_page = 1, $post = null){
	$length = get_post_length($post);
	if($length < 1){
		echo $placeholder;
	}else{
		echo $prefix.number_format_i18n(max(array(1, round($length / $per_page)))).$suffix;
	}
}

/**
 * 投稿の平均的な文字数を調べる
 *
 * @global wpdb $wpdb
 * @param itn $parent_id
 * @return int 
 */
function get_post_length_avg($parent_id = 0){
	global $wpdb;
	$sql = <<<EOS
		SELECT AVG(CHAR_LENGTH(post_content)) FROM {$wpdb->posts}
		WHERE post_status = 'publish' AND post_type = 'post'
EOS;
	if($parent_id){
		$sql .= ' AND '.$wpdb->prepare('post_parent = %d', $parent_id);
	}
	return $wpdb->get_var($sql);
}


/**
 * 指定されたユーザーが投稿を行っているか
 * @global wpdb $wpdb
 * @param int $user_id
 * @param string $post_type
 * @param int $days
 * @return boolean 
 */
function has_recent_post($user_id, $post_type = 'post', $days = 30){
	global $wpdb;
	$sql = <<<EOS
		SELECT ID FROM {$wpdb->posts}
		WHERE post_type = %s AND post_author = %d AND post_status = 'publish'
		  AND ( TO_DAYS(NOW()) - TO_DAYS(post_date) <= %d )
		LIMIT 1
EOS;
	return (boolean)$wpdb->get_var($wpdb->prepare($sql, $post_type, $user_id, $days));
}

/**
 * 投稿がいつのものかを出力する
 *
 * @param object $post
 */
function the_post_time_diff($modified = false, $post = null){
	$post = get_post($post);
	echo human_time_diff_jp(strtotime(($modified ? $post->post_modified : $post->post_date)));
}

/**
 * human_time_diffを日本語対応にしたもの
 *
 * @param int $from 
 * @param string $to
 * @return string
 */
function human_time_diff_jp($from, $to = ''){
	$diff = human_time_diff($from, $to);
	if(strpos($diff, '日') !== false){
		$days = intval($diff);
		if($diff < 365){
			$diff = floor($days / 30).'ヶ月';
			if($days % 30 > 15){
				$diff .= '半';
			}
		}else{
			$diff = floor($days / 365).'年';
			if($days % 365 > 180)
			$diff .= '半';
		}
	}
	return $diff.'前';
}


/**
 * 投稿が新しいか否か
 * @param int $offset 初期値は7
 * @param object $post
 * @return boolean
 */
function is_new_post($offset = 7, $post = null){
	$post = get_post($post);
	return (current_time('timestamp') - strtotime($post->post_date)) < 60 * 60 * 24 * $offset;
}

/**
 * 子投稿の数を返す
 * @global wpdb $wpdb
 * @param string $post_type
 * @param string $status
 * @param object $post
 * @return int
 */
function get_post_children_count($post_type = 'post', $status = 'publish', $post = null){
	global $wpdb;
	$post = get_post($post);
	return (int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = %s AND post_parent = %d", $post_type, $status, $post->ID));
}

/**
 * 左サイドバーが必要か否か
 * @return boolean
 */
function needs_left_sidebar(){
	return  (boolean)(
		(is_archive() &&
			!(is_tax('faq_cat') || is_post_type_archive('faq') || is_tax('topic') || is_post_type_archive('thread'))
		)
			||
		(is_search() &&
			!(is_post_type_archive('faq') || is_post_type_archive('thread'))
		)
			||
		is_404()
			||
		is_home()
			||
		is_singular('series')
	);
}