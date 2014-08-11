<?php
/**
 * シリーズに関する処理／関数群
 */



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
 * シリーズを選ぶセレクトボックスを表示する
 * @global object $post
 * @global wpdb $wpdb
 */

add_action('psost_submitbox_misc_actions', function(){
    $screen = get_current_screen();
    if( $screen->post_type == 'post' ){
        $current_post_parent = $post->post_parent;
        ?>
        <div class="misc-pub-section misc-pub-section-last series-setter">
            <?php
            $serieses = $wpdb->get_results($wpdb->prepare("SELECT ID, post_title, post_parent FROM {$wpdb->posts} WHERE post_type = 'series' AND post_author = %d ORDER BY ID ASC", $post->post_author));
            if(!empty($serieses)):
                ?>
                <label>
                    作品集名:
                    <select name="series_id">
                        <option value="0"<?php if($current_post_parent == 0) echo ' selected="selected"';?>>なし</option>
                        <?php foreach($serieses as $series): ?>
                            <option value="<?php echo $series->ID; ?>"<?php if($current_post_parent == $series->ID) echo ' selected="selected"';?>><?php echo esc_html($series->post_title); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            <?php else: ?>
                <label>作品集名: まだ作品集を<a href="<?php echo admin_url('post-new.php?post_type=series');?>">登録</a>していません。</label>
            <?php endif; ?>
        </div>
    <?php
    }
});


/**
 * 投稿にシリーズを付与する
 * @param int $post_id
 */
function _hametuha_set_series_to($post_id){
    if(wp_is_post_autosave($post_id) || wp_is_post_revision( $post_id )){
        return;
    }
    if(isset($_REQUEST['series_id'], $_REQUEST['post_ID']) && $post_id == $_REQUEST['post_ID']){
        remove_action('save_post', '_hametuha_set_series_to');
        $req = wp_update_post(array(
            'ID' => intval($_REQUEST['post_ID']),
            'post_parent' => intval($_REQUEST['series_id'])
        ));
    }
}
add_action('save_post', '_hametuha_set_series_to');

/**
 * リダイレクトされるのを防ぐ
 *
 * @param string $redirect_url
 * @return string
 */
function _hametuha_canonical($redirect_url){
    if( is_singular('series') && false !== strpos($_SERVER['REQUEST_URI'], '/page/') ){
        return false;
    }else{
        return $redirect_url;
    }
}
add_filter('redirect_canonical', '_hametuha_canonical');
