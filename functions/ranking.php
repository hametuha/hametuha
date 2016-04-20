<?php

use \Hametuha\QueryHighJack\RankingQuery;


/**
 * ランキングURLのショートコード
 * @param array $atts
 * @param string $content
 * @return string
 */
add_shortcode('ranking_url', function($atts = [], $content = ''){
	foreach( [
		'url'  => home_url('/ranking/weekly/'.get_latest_ranking_day('Ymd/')),
	    'date' => get_latest_ranking_day(get_option('date_format')),
	] as $key => $repl){
		$content = str_replace('%'.$key.'%', $repl, $content);
	}
	return $content;
});

/**
 * ランキングを出力する
 *
 * @param WP_Post $post
 */
function the_ranking( \WP_Post $post = null){
    echo number_format(get_the_ranking($post));
}

/**
 * 最新の週間ランキング日を取得する
 *
 * @param string $format
 *
 * @return string
 */
function get_latest_ranking_day($format = ''){
	$thursday = date_i18n('N')  == 4 ? current_time('timestamp') : strtotime("Previous Thursday", current_time('timestamp'));
	$sunday = strtotime('Previous Sunday', $thursday);
	return date_i18n($format, $sunday);
}

/**
 * ランキングを取得する
 *
 * @param WP_Post $post
 * @return int
 */
function get_the_ranking( \WP_Post $post = null){
    $post = get_post($post);
    return isset($post->rank) ? $post->rank : 1;
}

/**
 * ランキングページか否か
 *
 * @param string $type
 * @return bool
 */
function is_ranking($type = ''){
    if( $ranking = get_query_var('ranking') ){
        switch( $type ){
            case 'yearly':
            case 'monthly':
            case 'daily':
            case 'weekly':
            case 'top':
            case 'best':
                return $type == $ranking;
                break;
            default:
                if( empty($type) ){
                     return true;
                }else{
                    return false;
                }
                break;
        }
    }else{
        return false;
    }
}


/**
 * ランキングのクラスを返す
 *
 * @param int $rank
 * @return string
 */
function ranking_class($rank){
    switch($rank){
        case 1:
            return ' king';
            break;
        case 2:
        case 3:
            return ' ranker';
            break;
        default:
            return ' normal';
            break;
    }
}

/**
 * 確定済みのランキングか否か
 *
 * @return bool
 */
function is_fixed_ranking(){
    if( is_ranking('yearly') ){
        return get_query_var('year') < date_i18n('Y');
    }elseif( is_ranking('monthly') ){
        // 現在の日時が翌月3日以降かをチェック
        return current_time('timestamp') > strtotime(sprintf('%d-%02d-03 00:00:00', get_query_var('year'), (get_query_var('monthnum') + 1)));
    }elseif( is_ranking('weekly') ){
        // 指定された曜日が最終日曜日よりも前か否か
        return strtotime(sprintf('%d-%02d-%02d 00:00:00', get_query_var('year'), get_query_var('monthnum'), get_query_var('day'))) <= strtotime('Previous Sunday', strtotime('Previous Thursday', current_time('timestamp')));
    }elseif( is_ranking('daily') ){
        // 基本OK
        return current_time('timestamp') > strtotime(sprintf('%d-%02d-%02d 00:00:00', get_query_var('year'), get_query_var('monthnum'), (get_query_var('day') + 3)));
    }else{
        return false;
    }
}

/**
 * ランキングのタイトルを返す
 *
 * @return string
 */
function ranking_title(){
    switch( get_query_var('ranking') ){
        case 'yearly':
            return sprintf('%d年間ランキング', get_query_var('year'));
            break;
        case 'monthly':
            return sprintf('%d年%d月間ランキング', get_query_var('year'), get_query_var('monthnum'));
            break;
        case 'daily':
            return sprintf('%d年%d月%d日のランキング', get_query_var('year'), get_query_var('monthnum'), get_query_var('day'));
            break;
        case 'weekly':
            return sprintf('%d年%d月%d日までの週間ランキング', get_query_var('year'), get_query_var('monthnum'), get_query_var('day'));
            break;
        case 'best':
            $title = '歴代ベスト';
            if( $slug = get_query_var('category_name') ){
                $cat = get_category_by_slug($slug);
                $title .= sprintf('（%s部門）', esc_html($cat->name));
            }
            return $title;
            break;
        default:
            return 'ランキング';
            break;
    }
}

