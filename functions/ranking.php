<?php

use \Hametuha\QueryHighJack\RankingQuery;

/**
 * ランキングを出力する
 *
 * @param WP_Post $post
 */
function the_ranking( \WP_Post $post = null){
    echo number_format(get_the_ranking($post));
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
 * @return bool
 */
function is_ranking($type = ''){
    if( $ranking = get_query_var('ranking') ){
        switch( $type ){
            case 'yearly':
            case 'monthly':
            case 'daily':
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
        default:
            return 'ランキング';
            break;
    }
}

