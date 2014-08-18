<?php
/**
 * 静的コンテンツの読み込みをSSLに対応させるためのリンク
 */


/**
 * URLをCDNかつSSL対応にする
 *
 * @param string $src
 * @return string
 */
function hametuha_cdnfy($src){
    if( is_ssl() ){
        $src = str_replace('http://', 'https://', $src);
    }
    if( !is_admin() ){
        $src = str_replace('://hametuha', '://s.hametuha', $src);
    }
    return $src;
}

/**
 * SSLのコンテンツが表示されているときにsrcなどを修正する
 *
 * @param string $content
 * @return string
 */
add_filter('the_content', function($content){
    $upload_dir = wp_upload_dir();
    $upload_dir_url = $upload_dir['baseurl'];
    $upload_dir_cdn_url = hametuha_cdnfy($upload_dir_url);
    if( $upload_dir_cdn_url != $upload_dir_url ){
        $content = str_replace($upload_dir_url, $upload_dir_cdn_url, $content);
    }
    return $content;
});

/**
 * Image widgetのURLをSSLに変更
 */
add_filter('wp_get_attachment_url', 'hametuha_cdnfy');

/**
 * テーマディレクトリのURLをCDN対応にする
 *
 * @param string $url
 * @return string
 */
function _hametuha_cdn_url($url){
    if( !is_admin() ){
        $url = preg_replace("/:\/\//", '://s.', $url);
    }
    return $url;
}
add_filter('template_directory_uri', '_hametuha_cdn_url');
add_filter('stylesheet_directory_uri', '_hametuha_cdn_url');


/**
 * wp_enqueue_scriptで読み込まれたJavascriptのSRC属性をCDN対応
 *
 * @param string $src
 * @return string
 */
add_filter('script_loader_src', function($src){
    $home_url = home_url();
    if( false !== strpos($src, $home_url) ){
        $src = hametuha_cdnfy($src);
    }
    return $src;
});

/**
 * CSSのURLを書き換える
 *
 * @param string $tag
 * @return string
 */
add_filter('style_loader_tag', function($tag, $handle){
    switch( $handle ){
        case 'hametuha-app':
            // メインのCSSは同じドメインに直す
            $tag = str_replace('://s.hametuha', '://hametuha', $tag);
            break;
        default:
            // 同一ドメインのものだけURLを書き換え
            $url = "href='".home_url();
            if( false !== strpos($tag, 'href=\''.home_url()) ){
                $tag = hametuha_cdnfy($tag);
            }
            break;
    }
    return $tag;
}, 10, 2);
