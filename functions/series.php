<?php
/**
 * シリーズに関する処理／関数群
 */

use Hametuha\Model\Series;

/**
 * シリーズに属しているか否かを返す。属している場合は親ID
 *
 * @param WP_Post $post
 * @return int
 */
function is_series( $post = null ){
    $post = get_post($post);
    return 'series' == get_post_type($post->post_parent) ? $post->post_parent : 0;
}


/**
 * シリーズに属している場合にシリーズページへのリンクを返す
 *
 * @param string $pre
 * @param string $after
 * @param WP_Post $post
 */
function the_series($pre = '', $after = '', $post = null){
    $series = is_series($post);
    if( $series ){
        $series = get_post($series);
        echo $pre.'<a href="'.get_permalink($series->ID).'">'.apply_filters("the_title", $series->post_title).'</a>'.$after;
    }
}

/**
 * Get all user for series
 *
 * @param null|WP_Post|int $post
 *
 * @return array
 */
function get_series_authors($post = null){
	$post = get_post($post);
	return Series::get_instance()->get_authors($post->ID);
}

/**
 * リダイレクトされるのを防ぐ
 *
 * @param string $redirect_url
 * @return string
 */
add_filter('redirect_canonical', function($redirect_url){
    if( is_singular('series') && false !== strpos($_SERVER['REQUEST_URI'], '/page/') ){
        return false;
    }else{
        return $redirect_url;
    }
} );




/**
 * シリーズをみられないようにする
 *
 * @param string $content
 *
 * @return string
 */
function hametuha_series_hide($content){
	// DOMの一部を切り出す
	$dom = \WPametu\Utility\Formatter::get_dom($content);
	$body = $dom->getElementsByTagName('body')->item(0);
	$dom_count = $body->childNodes->length;
	$limit = floor( $dom_count / 4 );
	for( $i = $dom_count - 1; $i >= 0; $i-- ){
		if( $i > $limit ){
			$body->removeChild($body->childNodes->item($i));
		}
	}
	$content = \WPametu\Utility\Formatter::to_string($dom);
	$content .= "\n<div class=\"content-hide-cover\"></div>";
	remove_filter('the_content', 'hametuha_series_hide');
	return $content;
}

/**
 * カラムを追加
 */
add_filter('manage_posts_columns', function($columns, $post_type){
	if( 'series' == $post_type ){
		$new_columns = [];
		foreach( $columns as $key => $val ){
			if( 'author' == $key){
				$val = '編集者';
			}
			$new_columns[$key] = $val;
			if( 'title' == $key ){
				$new_columns['count'] = '作品数';
				$new_columns['sales_status'] = '販売状況';
			}
		}
		$columns = $new_columns;
	}
	return $columns;
}, 10, 2);


add_action('manage_series_posts_custom_column', function($column, $post_id){
	switch( $column ){
		case 'count':
			$total = Series::get_instance()->get_total($post_id);
			if( $total ){
				printf('%s作品', number_format($total));
			}else{
				echo '<span style="color: lightgrey;">登録なし</span>';
			}
			break;
		case 'sales_status':
			$status = Series::get_instance()->get_status($post_id);
			switch($status){
				case 2:
					$color = 'green';
					break;
				case 1:
					$color = 'orange';
					break;
				default:
					$color = 'lightgrey';
					break;
			}
			printf("<span style='color: %s'>%s</span>", $color, Series::get_instance()->status_label[$status]);
			if( $asin = Series::get_instance()->get_asin($post_id) ){
				echo "<code>{$asin}</code>";
			}
			break;
		default:
			// Do nothing.
			break;
	}
}, 10, 2);
