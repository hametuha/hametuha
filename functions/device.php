<?php


/**
 * bodyにクラスを付与する
 * @param array $class
 * @return array
 */
function _hametuha_body_class($class){
	if(wp_is_mobile()){
		$class[] = 'mobile';
	}
	return $class;
}
add_action('body_class', '_hametuha_body_class');

/**
 * Viewportを出力
 */
function _hametuha_mobile_viewport(){
	if(wp_is_mobile() && (
		is_singular('post')
			||
		is_singular('series')
			||
		is_post_type_archive('series')
			||
		is_singular('thread')
			||
		is_tax('topic')
			||
		is_post_type_archive('thread')
	)){
?>
<meta name="viewport" content="width=800px" />
<?php
	}
}
add_action('wp_head', '_hametuha_mobile_viewport', 1);