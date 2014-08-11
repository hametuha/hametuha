<?php

/**
 * エディタースタイルを適用する
 *
 * 投稿以外と投稿でCSSを分ける
 */
add_action( 'admin_head', function() {
    $screen = get_current_screen();
    if( $screen && $screen->post_type == 'post' ){

    }else{
        add_editor_style('assets/css/editor-style.css');
    }
});



/**
 * TinyMCEの初期化配列を作成する
 * @param array $initArray
 * @return array
 */
function _hametuha_tinyMCE($initArray) {
     //選択できるブロック要素を変更
    $initArray['theme_advanced_blockformats'] = 'p,h2,h3,h4,h5,dt,dd';
	//使用できるタグを指定
	if(empty($initArray['extended_valid_elements' ])){
		$initArray[ 'extended_valid_elements' ] = "iframe[id|class|title|style|align|frameborder|height|longdesc|marginheight|marginwidth|name|scrolling|src|width]";
	}else{
		$elements = explode(',', $initArray[ 'extended_valid_elements' ]);
		$elements[] = "iframe[id|class|title|style|align|frameborder|height|longdesc|marginheight|marginwidth|name|scrolling|src|width]";
		$initArray[ 'extended_valid_elements' ] = implode(',', $elements);
	}	
	//スタイル
	$initArray['style_formats'] = json_encode(array(
		array(
			'title' => '圏点',
			'inline' => 'span',
			'classes' => 'text-emphasis'
		),
		array(
			'title' => '太字+圏点',
			'inline' => 'strong',
			'classes' => 'text-emphasis'
		)
	));
    return $initArray;
}
add_filter('tiny_mce_before_init', '_hametuha_tinyMCE', 10001);

add_action('edit_form_after_title', function($post){
    if( 'post' == $post->post_type ){
        echo '<h3>本文</h3>';
    }
}, 10000);