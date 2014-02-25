<?php

/**
 * 一連のシェアボタンを出力する
 * @param string $title
 * @param string $url 
 * @param string|array $class_name クラス名
 * @param boolean $small trueにすると小さいアイコン
 */
function hametuha_share($title, $url, $class_name = '', $small = false){
	$class_name = (array)$class_name;
	array_unshift($class_name, 'like');
	$fb_url = urlencode($url);
	if($small){
		$class_name[] = 'small-like';
		$fb_layout = 'button_count';
		$twt_layout = 'horizontal';
		$g_layout = 'standard';
		$hatena_layout = 'standard';
		$mixi_layout = 'medium';
	}else{
		$fb_layout = 'box_count';
		$twt_layout = 'vertical';
		$g_layout = 'tall';
		$hatena_layout = 'vertical';
		$mixi_layout = 'large';
	}
	$class_name = implode(' ', array_map('esc_attr', $class_name));
	
	echo <<<EOS
<div class="{$class_name}">
	<!-- Facebook -->
	<div class="fb-like" data-href="{$url}" data-width="72" data-layout="{$fb_layout}" data-show-faces="false" data-send="false"></div>
	<!-- twitter -->
	<a href="https://twitter.com/share" class="twitter-share-button" data-count="{$twt_layout}" data-url="{$url}" data-text="{$title}" data-via="hametuha" data-lang="ja" data-related="minico_me" data-hashtags="破滅派">ツイート</a>
	<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
	<!-- Google + -->
	<g:plusone size="{$g_layout}" href="{$url}"></g:plusone>
EOS;
	if(!is_ssl()){
		echo <<<EOS
	<!-- Hatena -->
	<a href="http://b.hatena.ne.jp/entry/{$url}" class="hatena-bookmark-button" data-hatena-bookmark-title="{$title}" data-hatena-bookmark-layout="{$hatena_layout}" data-hatena-bookmark-lang="ja" title="このエントリーをはてなブックマークに追加"><img src="http://b.st-hatena.com/images/entry-button/button-only@2x.png" alt="このエントリーをはてなブックマークに追加" width="20" height="20" style="border: none;" /></a>
	<script type="text/javascript" src="http://b.st-hatena.com/js/bookmark_button.js" charset="utf-8" async="async"></script>
EOS;
	}
	echo <<<EOS
</div>
EOS;
}



/**
 * Facebook用のスクリプトを書き出す
 */
function hametuha_fb_root(){
	?>
	<div id="fb-root"></div>
	<script>(function(d, s, id) {
	  var js, fjs = d.getElementsByTagName(s)[0];
	  if (d.getElementById(id)) return;
	  js = d.createElement(s); js.id = id;
	  js.src = "//connect.facebook.net/ja_JP/all.js#xfbml=1&appId=196054397143922";
	  fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));</script>
	<?php
}



/**
 * OGPを出力する
 */
function _hametuha_ogp(){
	if(is_front_page() || is_singular() || is_post_type_archive() || is_author()){
		//画像の初期値を設定
		$image = get_template_directory_uri()."/img/facebook-logo.jpg";
		//個別設定
		if(is_front_page()){
			$title = get_bloginfo('name');
			$url = trailingslashit(get_bloginfo('url'));
			$type = "website";
			$desc = str_replace("\n", "", get_bloginfo('description'));
			$author = '';
		}elseif(is_post_type_archive()){
			$post_obj = get_post_type_object(get_post_type());
			$title = wp_title('|', false, "right").get_bloginfo('name');
			$url = get_post_type_archive_link(get_post_type());
			$type = 'article';
			$desc = $post_obj->description;
			$author = '';
			switch(get_post_type()){
				case 'anpi':
					$image = get_template_directory_uri().'/img/banner-anpi-about.jpg';
					break;
				case 'thread':
					$image = get_template_directory_uri().'/img/facebook-logo-bbs.jpg';
					break;
			}
		}elseif(is_author()){
			global $wp_query;
			$user = get_userdata($wp_query->query_vars['author']);
			$title = $user->display_name;
			$type = 'profile';
			$url = get_author_posts_url($user->ID, $user->user_nice_name);
			$image = preg_replace("/^.*src=[\"']([^\"']+)[\"'].*$/", '$1', get_avatar($user->ID, 150));
			$desc = str_replace("\n", "", get_user_meta($user->ID, 'description', true));
			$author = '<meta property="profile:username" content="'.$user->user_login.'" />';
		}else{
			the_post();
			$title = wp_title('|', false, "right").get_bloginfo('name');
			$url =  get_permalink();
			$type =  'article';
			$desc = str_replace("\n", "", get_the_excerpt());
			$author = '<meta property="article:author" content="'.  get_author_posts_url(get_the_author_ID()).'" />';
			if(is_singular('thread')){
				$image = preg_replace("/^.*src=[\"']([^\"']+)[\"'].*$/", '$1', get_avatar(get_the_author_meta('ID'), 150));
			}elseif(has_post_thumbnail()){
				$image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'large');
				$image = $image[0];
			}else{
				$images = get_children("post_parent=".get_the_ID()."&post_mime_type=image&orderby=menu_order&order=ASC&posts_per_page=1");
				if(!empty($images)){
					$image = wp_get_attachment_image_src(current($images)->ID, 'large');
					$image = $image[0];
				}
			}
			rewind_posts();
		}
		echo <<<EOS
<meta name="twitter:card" content="summary">
<meta name="twitter:site" content="@hametuha">
<meta property="og:title" content="{$title}"/>
<meta property="og:url" content="{$url}" />
<meta property="og:image" content="{$image}" />
<meta property="og:description" content="{$desc}" />
<meta name="description" content="{$desc}" />
<meta property="og:type" content="{$type}" />
{$author}
<meta property="og:site_name" content="破滅派｜オンライン文芸誌" />
<meta property="og:locale" content="ja_jp" />
<meta property="fb:app_id" content="196054397143922" />
<meta property="fb:admins" content="1034317368" />
EOS;
	}
}
add_action('wp_head', '_hametuha_ogp', 1);


/**
 * Google plus 用のJSを読み込み
 * 
 */
function _hametuha_footer_plusone(){
	?>
<script type="text/javascript">
	window.___gcfg = {
		lang: 'ja'
	};
	(function() {
		var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
		po.src = 'https://apis.google.com/js/plusone.js';
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
	})();
</script>
	<?php
}
add_action('wp_footer', '_hametuha_footer_plusone', 1);


/**
 * RSSへのリンクをヘッダーに出力
 */
function _hametuha_rss(){
	?>
<link rel="alternate" type="application/rss+xml" title="おしらせRSS｜<?php bloginfo('name'); ?>" href="http://feeds.feedburner.com/hametuha" />
	<?php
}
add_action('wp_head', '_hametuha_rss');


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
						$author = get_author_name($post->post_author);
						$string = "{$author}さんが新作「{$post->post_title}」を投稿しました {$url}";
						update_twitter_status($string);
						break;
					case 'announcement':
						$url = home_url()."?p={$post->ID}";
						if(user_can($post->post_author, 'edit_others_posts')){
							$string = "破滅派編集部からのお知らせです > {$post->post_title} {$url}";
						}else{
							$author = get_author_name($post->post_author);
							$string = "{$author}さんから告知があります > {$post->post_title} {$url}";
						}
						update_twitter_status($string);
						break;
					case 'anpi':
						$url = home_url()."?p={$post->ID}";
						$author = get_author_name($post->post_author);
						$string = "{$author}さんの安否です。「{$post->post_title}」 {$url}";
						update_twitter_status($string);
						break;
					case 'info':
						$url = home_url()."?p={$post->ID}";
						update_twitter_status("お知らせ > {$post->post_title} {$url}");
						break;
					case 'thread':
						$url = home_url()."?p={$post->ID}";
						$author = get_author_name($post->post_author);
						update_twitter_status("{$author}さんがBBSにスレッドを立てました > {$post->post_title} {$url}");
						break;
				}
				break;
		}
	}
}
add_action('transition_post_status', '_hametuha_publish_tweet', 10, 3);


/**
 * Pixivの埋め込みタグを出力する
 * @global object $post
 * @param mixed $post 
 */
function pixiv_output($post = null){
	if(is_null($post)){
		global $post;
	}else{
		$post = get_post($post);
	}
	$meta = get_post_meta($post->ID, '_pixiv_embed', true);
	if($meta){
		$match = array();
		if(preg_match("/data-id=\"([^\"]+)\"/", $meta, $match)){
			echo '<script src="http://source.pixiv.net/source/embed.js" data-id="'.esc_attr($match[1]).'" data-size="large" data-border="off" charset="utf-8"></script>';
		}
	}
}

/**
 * 投稿がPixivタグを持っているか否かを返す
 * @global object $post
 * @param object $post
 * @return boolean
 */
function has_pixiv($post = null){
	if(is_null($post)){
		global $post;
	}else{
		$post = get_post($post);
	}
	return (boolean)get_post_meta($post->ID, '_pixiv_embed', true);
}

/**
 * サムネイル用のメタボックスに表示するHTML
 * @global int $post_ID
 * @param string $content
 * @return string
 */
function _pixiv_metabox($content){
	global $post_ID;
	$img_url = get_template_directory_uri()."/img/icon-help-small.png";
	$help_url = home_url('/faq/pixiv-embed/');
	$nonce = wp_create_nonce('pixiv_embed_nonce');
	$embed = (!$post_ID) ? '' : esc_attr(get_post_meta($post_ID, '_pixiv_embed', true));
	$output = <<<EOS
		<p class="description"><strong>または</strong><br />Pixivの画像を貼付ける</p>
		<input type="hidden" name="pixiv_embed_nonce" value="{$nonce}" />
		<label for="pixiv-embed-tag">埋め込みタグ<a href="{$help_url}" target="_blank" title="Pixivのembedタグについてはこちらをクリック"><img src="{$img_url}" height="16" width="16" /></a></label>
		<input type="text" name="pixiv_embed_tag" id="pixiv_embed_tag" value="{$embed}" />
EOS;
	return $content.$output;
}
add_filter('admin_post_thumbnail_html', '_pixiv_metabox');


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