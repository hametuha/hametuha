<?php

/**
 * シェア数カウント用のAPIを設定する
 */
add_action("admin_init", function(){
	if( defined('DOING_AJAX') && DOING_AJAX ){
		foreach(['wp_ajax_', 'wp_ajax_nopriv_'] as $action){
			add_action("{$action}hametuha_share_count", '_hametuha_share_count');
		}
	}
});


/**
 * シェア数を取得する
 *
 * @internal
 */
function _hametuha_share_count(){
	$json = [
		'success' => false,
	    'result'  => []
	];
	try{
		$post_id = isset($_GET['post_id']) ? (int) $_GET['post_id'] : 0;
		$key = "share_count_{$post_id}";
		$result = get_transient($key);
		if( '0' == $result ){
			if( !$post_id ){
				$permalink = home_url('/', 'http');
			}else{
				if( !($post = get_post($post_id) ) ){
					throw new Exception('投稿は存在しません', 404);
				}
				$permalink = get_permalink($post);
			}
			$result = [];
			$url = rawurlencode($permalink);
			foreach([
				'facebook' => 'http://graph.facebook.com/?id='.$url,
				'twitter'  => 'http://urls.api.twitter.com/1/urls/count.json?url='.$url,
			    'hatena'   => 'http://api.b.st-hatena.com/entry.count?url='.$url,
			    'googleplus' => 'https://clients6.google.com/rpc?key=AIzaSyCKSbrvQasunBoV16zDH9R33D88CeLr9gQ',
			    'pocket'   => 'http://widgets.getpocket.com/v1/button?v=1&count=horizontal&url='.$url,
			] as $brand => $endpoint){
				if( 'googleplus' == $brand ){
					$ch = curl_init();
					curl_setopt( $ch, CURLOPT_URL, $endpoint );
					curl_setopt( $ch, CURLOPT_POST, 1 );
					curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
					curl_setopt( $ch, CURLOPT_TIMEOUT, 5);
					curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode([
						[
							'method' => 'pos.plusones.get',
							"id" => "p",
							"params" => [
								"nolog" => true,
								"id" => $permalink,
								"source" => "widget",
								"userId" => "@viewer",
								"groupId" => "@self"
							],
							"jsonrpc" => "2.0",
							"key" => "p",
							"apiVersion" => "v1",
						]
					]));
					curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
					curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-type: application/json' ) );
					$response = curl_exec( $ch );
					curl_close( $ch );
					//JSONデータからカウント数を取得
					$obj = json_decode( $response, true );
					//カウント(データが存在しない場合は0扱い)
					if(!isset($obj[0]['result']['metadata']['globalCounts']['count'])){
						$cnt = 0;
					}else{
						$cnt = $obj[0]['result']['metadata']['globalCounts']['count'];
					}
				}else{
					$response = wp_remote_get($endpoint);
					if( is_wp_error($response) ){
						$result[$brand] = 'N/A';
						continue;
					}
					switch( $brand ){
						case 'facebook':
							$res = json_decode($response['body']);
							$cnt = ( isset($res->shares) && is_numeric($res->shares) ) ? intval($res->shares) : 0;
							break;
						case 'twitter':
							$res = json_decode($response['body']);
							$cnt = isset($res->count) ? intval($res->count) : 0;
							break;
						case 'hatena':
							$cnt = is_numeric($response['body']) ? (int) $response['body'] : 0;
							break;
						case 'pocket':
							$cnt = 0;
							if( preg_match('#<em id="cnt">([0-9]+)</em>#u', $response['body'], $match) ){
								$cnt = (int) $match[1];
							}
							break;
						default:
							// Do nothing
							$cnt = 0;
							continue;
							break;
					}
				}
				$result[$brand] = $cnt;
				if( $cnt && $post_id ){
					update_post_meta($post_id, "_sns_count_{$brand}", $cnt);
				}
			}
			set_transient($key, $result, 60 * 30);
		}
		$json['result'] = $result;
		$json['success'] = true;
	}catch ( Exception $e ){
		$json['message'] = $e->getMessage();
	}
	wp_send_json($json);
}


/**
 * 一連のシェアボタンを出力する
 *
 * @param WP_Post $post 指定しない場合は現在の投稿
 * @return array
 */
function hametuha_share($post = null){
	if( is_singular() ){
	    $post = get_post($post);
	    $title = get_the_title($post);
	    $url = get_permalink($post);
		$fb_url = $url;
		$post_id = $post->ID;
	}else{
		$title = get_bloginfo('name');
		$url = home_url('/');
		$fb_url = '';
		$post_id = 0;
	}
	$encoded_url = rawurlencode($url);
    $encoded_title = rawurlencode($title.' | 破滅派');
    $hash_tag = rawurlencode('#破滅派');
    $data_title = esc_attr($title);
	$links = [];
    foreach( [
        ['facebook', $url, '4', false],
        ['twitter', "https://twitter.com/home?status={$encoded_title}%20{$encoded_url}%20{$hash_tag}&amp;via=@hametuha", '3', true],
        ['googleplus', "https://plus.google.com/share?url={$encoded_url}", '4', true],
        ['hatena', "http://b.hatena.ne.jp/add?title={$encoded_title}&amp;url={$encoded_url}", '', true],
        ['pocket', "http://getpocket.com/edit?url={$encoded_url}&amp;title={$encoded_title}", '', true],
        ['line', "line://msg/text/{$encoded_title}%20{$encoded_url}", '', false],
    ] as list($brand, $href, $suffix, $blank) ){
	    $links[$brand] = sprintf(
		    '<a class="share share--%1$s share-retrieve" data-medium="%1$s" data-target="%2$d" href="%3$s"%5$s>
				<i class="icon-%1$s%4$s"></i>
				%6$s
			</a>',
            $brand,
		    $post_id,
		    $href,
		    $suffix,
		    ( $blank ? ' target="_blank"' : '' ),
		    ('line' != $brand ? '<span>---</span>' : '<span>送る</span>')
	    );
    }
	return $links;
}

/**
 * Google Adsenseを出力する
 *
 * @param int $unit_no
 * @deprecated
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
 * 投稿が公開されたときにつぶやく
 *
 * @param string $new_status
 * @param string $old_status
 * @param object $post
 */
add_action('transition_post_status', function($new_status, $old_status, $post){
	//はじめて公開にしたときだけ
	if( !WP_DEBUG && $new_status == 'publish' && function_exists('update_twitter_status') ){
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
}, 10, 3);

