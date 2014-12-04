<?php

/**
 * 一連のシェアボタンを出力する
 *
 * @param WP_Post $post 指定しない場合は現在の投稿
 */
function hametuha_share($post = null){
    $post = get_post($post);
    $title = get_the_title($post);
    $url = get_permalink($post);
	$encoded_url = rawurlencode($url);
    $encoded_title = rawurlencode($title.' | 破滅派');
    $hash_tag = rawurlencode('#破滅派');
    $data_title = esc_attr($title);
    foreach( [
        ['facebook', '#', '4'],
        ['twitter', "https://twitter.com/home?status={$encoded_title}%20{$encoded_url}%20{$hash_tag}", '3'],
        ['googleplus', "https://plus.google.com/share?url={$encoded_url}", '4'],
        ['hatebu', "http://b.hatena.ne.jp/add?title={$encoded_title}&amp;url={$encoded_url}", ''],
        ['line', "line://msg/text/{$encoded_title}%20{$encoded_url}", '']
    ] as list($brand, $href, $suffix) ){
	    printf('<a class="share" data-medium="%1$s" data-target="%2$d" href="%3$s"><i class="icon-%1$s%4$s"></i></a>',
               $brand,
		    get_the_ID(),
		    $href,
		    $suffix);
    }
}

/**
 * Google Adsenceを出力する
 *
 * @param int $unit_no
 */
function google_adsense( $unit_no = 1 ){
    switch($unit_no){
        default:
            echo <<<EOS
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- 破滅派記事直後 -->
<ins class="adsbygoogle"
     style="display:block"
     data-ad-client="ca-pub-0087037684083564"
     data-ad-slot="4859005648"
     data-ad-format="auto"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
EOS;
            break;
    }
}

/**
 * Facebook用のスクリプトを書き出す
 */
function hametuha_fb_root(){
	?>
	<?php
}



/**
 * 投稿が公開されたときにつぶやく
 * @param string $new_status
 * @param string $old_status
 * @param object $post
 */
function _hametuha_publish_tweet($new_status, $old_status, $post){
	//はじめて公開にしたときだけ
	if($new_status == 'publish' && function_exists('update_twitter_status')){
		switch($old_status){
			case 'new':
			case 'draft':
			case 'pending':
			case 'auto-draft':
			case 'future':
				switch($post->post_type){
					case 'post':
						$url = wp_get_shortlink($post->ID);
						$author = get_the_author_meta('display_name', $post->post_author);
						$string = "{$author}さんが新作「{$post->post_title}」を投稿しました {$url}";
						update_twitter_status($string);
						break;
					case 'announcement':
						$url = home_url()."?p={$post->ID}";
						if(user_can($post->post_author, 'edit_others_posts')){
							$string = "破滅派編集部からのお知らせです > {$post->post_title} {$url}";
						}else{
							$author = get_the_author_meta('display_name', $post->post_author);
							$string = "{$author}さんから告知があります > {$post->post_title} {$url}";
						}
						update_twitter_status($string);
						break;
					case 'anpi':
						$url = home_url()."?p={$post->ID}";
						$author = get_the_author_meta('display_name', $post->post_author);
						$string = "{$author}さんの安否です。「{$post->post_title}」 {$url}";
						update_twitter_status($string);
						break;
					case 'info':
						$url = home_url()."?p={$post->ID}";
						update_twitter_status("お知らせ > {$post->post_title} {$url}");
						break;
					case 'thread':
						$url = home_url()."?p={$post->ID}";
						$author = get_the_author_meta('display_name', $post->post_author);
						update_twitter_status("{$author}さんがBBSにスレッドを立てました > {$post->post_title} {$url}");
						break;
				}
				break;
		}
	}
}
add_action('transition_post_status', '_hametuha_publish_tweet', 10, 3);

