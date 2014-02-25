<?php
/**
 * wp_titleを変更 
 * @param string $title
 * @param string $sep
 * @param string $seplocation 
 */
function _hametuha_wp_title($title, $sep, $seplocation){
	if(is_singular('post')){
		global $post;
		$cats = get_the_category($post->ID);
		if(!empty($cats)){
			$cat = current($cats)->name;
		}else{
			$cat = "投稿";
		}
		$title .= "$cat {$sep} ";
	}elseif(is_singular('info')){
		$title .= "おしらせ {$sep} ";
	}elseif(is_singular('faq')){
		$title .= "よくある質問 {$sep} ";
	}elseif(is_singular('announcement')){
		$title .= "告知 {$sep} ";
	}elseif(is_singular('anpi')){
		$title .= "安否情報 {$sep} ";
	}elseif(is_singular('series')){
		$title .= "作品集 {$sep} ";
	}elseif(is_singular('thread')){
		$title .= "BBS {$sep} ";
	}elseif(is_category()){
		$title = "ジャンル: {$title}";
	}elseif(is_tag()){
		$title = "タグ: $title";
	}elseif(is_tax('faq_cat')){
		$title = "よくある質問: {$title}";
	}elseif(is_post_type_archive('thread')){
		$title = "破滅派BBS {$sep} ";
	}elseif(is_tax('topic')){
		$title = "破滅派BBSトピック: {$title}";
	}
	return $title;
}
add_filter('wp_title', '_hametuha_wp_title', 10, 3);

/**
 * 画像ファイルを表示していたページにおける後方置換
 * @param array $atts
 * @param string $content
 * @return string 
 */
function _hametuha_file($atts, $content = ''){
	extract(shortcode_atts(array(
		'href' => ''
	), $atts));
	return '<p class="center"><a target="_blank" href="'.$href.'" class="button flash-button">フルサイズで表示</a></p>';
}
add_shortcode('file', '_hametuha_file');

/**
 * 投稿ページでFlashのショートコードを含む場合のみスクリプトを載せる
 * @global object $post 
 */
function _hametuha_flash_script(){
	if(is_singular('post')){
		global $post;
		if(false !== strpos($post->post_content, '[flash')){
			wp_enqueue_script('hametuha-flash', get_bloginfo('template_directory').'/js/single-post-flash.js', array('swfobject'), HAMETUHA_THEME_VERSION, false);
			wp_localize_script('hametuha-flash', 'HametuhaFlash', array(
				'id' => 'hametuha-flash-content'
			));
		}
	}
}
add_action('template_redirect', '_hametuha_flash_script');

/**
 * ショートコードの後方互換
 * @param array $atts
 * @param string $content
 * @return string 
 */
function _hametuha_flash($atts, $content = ''){
	if(isset($atts[0])){
		return <<<EOS
<div class="flash-inline-container">
<object id="hametuha-flash-content" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="670" height="402">
	<param name="movie" value="{$atts[0]}" />
	<param name="wmode" value="transparent" />
	<!--[if !IE]>-->
	<object type="application/x-shockwave-flash" data="{$atts[0]}" width="670" height="402">
	<!--<![endif]-->
	<p class="message download-flash sans clearfix">
		<a href="http://www.adobe.com/go/getflashplayer"><img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player" /></a>
		このコンテンツをご覧になるにはAdobe Flash Playerが必要です。左のバナーからAdobeのサイトへ移動し、ダウンロードしてください。
	</p>
	<!--[if !IE]>-->
	</object>
	<!--<![endif]-->
</object>
</div>
<p class="center"><a target="_blank" href="{$atts[0]}" class="button flash-button">大きな画面で読む</a></p>
EOS;
	}else{
		return "";
	}
}
add_shortcode('flash', '_hametuha_flash');


/**
 * コメントリプライリンクのHTMLを変換する
 * @param string $html
 * @return string
 */
function _hametuha_reply_cancel_link($html){
	return str_replace("<a", '<a class="small-button"', $html);
}
add_filter('cancel_comment_reply_link', '_hametuha_reply_cancel_link');

/**
 * コメント返信ボタンにクラスを付与
 * @param string $html
 * @param object $comment
 * @param object $post
 * @return string
 */
function _hametuha_comment_reply_link($html, $comment = null, $post = null){
	return preg_replace("/(class=['\"])/", "$1button ", $html);
}
add_filter('comment_reply_link', '_hametuha_comment_reply_link');

/**
 * コメント編集ボタンにクラスを付与
 * @param string $html
 * @param int $comment_id
 * @return string
 */
function _hametuha_comment_edit_link($html, $comment_id = 0){
	return str_replace("class=\"", 'class="small-button ', $html);
}
add_filter('edit_comment_link', '_hametuha_comment_edit_link');


/**
 * コメント投稿メールを送信
 * @param string $message
 * @param int $comment_id
 * @return string
 */
function _hametuha_comment_message($message , $comment_id){
	//IPを削除
	$message = preg_replace("/\(IP.*$/m", "", $message);
	//Whoisを削除
	$message = preg_replace("/^Whois.*?$/m", "", $message);
	//メールアドレスを削除
	$message = preg_replace("/^メール:.*$/m", "", $message);
	return $message;
}
add_filter('comment_notification_text', '_hametuha_comment_message', 10, 2);

/**
 * コメント投稿時に送信されるメールヘッダーを修正
 * @param string $header
 * @param int $comment_id
 * @return string
 */
function _hametuha_comment_message_header($header , $comment_id){
	//FROMを変更
	$header = preg_replace("/From: \".*?\" <[^>]*>/", "From: \"破滅派｜オンライン文芸誌\" <info@hametuha.com>", $header);
	//Reply-Toを変更
	$header = preg_replace("/Reply-To: \".*?\" <[^>]*>/", "Reply-To: info@hametuha.com", $header);
	return $header;
}
add_filter('comment_notification_headers', '_hametuha_comment_message_header', 10, 2);


/**
 * Theme My Loginが出力するJavascriptのエラーを修正 
 */
function _hametuha_wp_attempt_focus_fix(){
	global $theme_my_login_object;
	if($theme_my_login_object && !is_page('login')){
		remove_action('wp_print_footer_scripts', array($theme_my_login_object, 'print_footer_scripts'));
	}
}
add_action('wp_print_footer_scripts', '_hametuha_wp_attempt_focus_fix', 1);

/**
 * Google Analyticator が吐き出すJSをプレビュー画面で停止 
 */
function _hametuha_fix_script_on_prview(){
	if(is_preview()){
		wp_dequeue_script('ga-external-tracking');
	}
}
add_action('wp_print_scripts', '_hametuha_fix_script_on_prview', 100000);