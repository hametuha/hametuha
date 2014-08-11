<?php
/**
 * アイキャッチに関する処理
 */


//投稿サムネイルを許可
add_theme_support('post-thumbnails', array('post', 'page', 'series', 'announcement'));
set_post_thumbnail_size( 640, 480, false );


/**
 * Pixivの埋め込みタグを出力する
 * @global object $post
 * @param mixed $post
 */
function pixiv_output($post = null){
    $post = get_post($post);
    if( $post && ($meta = get_post_meta($post->ID, '_pixiv_embed', true)) ){
        $match = array();
        if( preg_match("/data-id=\"([^\"]+)\"/", $meta, $match) ){
            echo '<script src="http://source.pixiv.net/source/embed.js" data-id="'.esc_attr($match[1]).'" data-size="large" data-border="off" charset="utf-8"></script>';
        }
    }
}

/**
 * 投稿がPixivタグを持っているか否かを返す
 *
 * @param object $post
 * @return boolean
 */
function has_pixiv($post = null){
    $post = get_post($post);
    return (boolean)get_post_meta($post->ID, '_pixiv_embed', true);
}

/**
 * サムネイル用のメタボックスに表示するHTML
 *
 * @param string $content
 * @param int $post_id
 * @return string
 */
add_filter('admin_post_thumbnail_html', function($content, $post_id){
    $help_url = home_url('/faq/pixiv-embed/');
    $nonce = wp_create_nonce('pixiv_embed_nonce');
    $embed = esc_textarea(get_post_meta($post_id, '_pixiv_embed', true));
    $output = <<<HTML
		<p class="description">または</p>
		<input type="hidden" name="pixiv_embed_nonce" value="{$nonce}" />
		<label for="pixiv-embed-tag">Pixivの画像を貼付ける<a href="{$help_url}" target="_blank" data-tooltip-title="Pixivのembedタグについてはこちらをクリック"><i class="dashicons dashicons-editor-help"></i></a></label>
		<textarea placeholder="埋め込みタグをここにコピペしてください" name="pixiv_embed_tag" id="pixiv_embed_tag">{$embed}</textarea>
HTML;
    return $content.$output;
}, 10, 2);


/**
 * PixivのIDを保存する
 * @param type $post_id
 */
function _pixiv_embed_save($post_id){
    if(isset($_REQUEST['pixiv_embed_nonce']) && wp_verify_nonce($_REQUEST['pixiv_embed_nonce'], 'pixiv_embed_nonce')){
        if(isset($_REQUEST['pixiv_embed_tag']) && !empty($_REQUEST['pixiv_embed_tag'])){
            update_post_meta($post_id, '_pixiv_embed', (string)$_REQUEST['pixiv_embed_tag']);
        }else{
            delete_post_meta($post_id, '_pixiv_embed');
        }
    }
}
add_action('save_post', '_pixiv_embed_save');