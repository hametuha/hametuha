<?php

/**
 * メディアは自分がアップロードしたものしか使えない
 *
 * @action pre_get_posts
 * @param WP_Query $wp_query
 */
add_filter('ajax_query_attachments_args', function ( array $query ) {
    // 現在のユーザーが投稿者じゃなければ、自分のだけに限定
    if( !current_user_can('edit_others_posts') ){
        $query['author'] =  get_current_user_id();
    }
    return $query;
});


// 管理画面でだけ実行
if( is_admin() ){

    /**
     * コメントは自分の投稿についたものだけ
     *
     * @action pre_get_comments
     * @param WP_Comment_Query &$comment_query
     */
    add_action('pre_get_comments', function( WP_Comment_Query &$comment_query ){
        $screen = get_current_screen();
        if( 'edit-comments' === $screen->id && !current_user_can('edit_others_posts') ){
            $comment_query->query_vars['post_author'] = get_current_user_id();
        }
    });


    /**
     * 投稿は自分のだけ
     *
     * @action pre_get_posts
     * @param WP_Query &$wp_query
     */
    add_action('pre_get_posts', function( WP_Query &$wp_query ){
        $screen = get_current_screen();
        if( $wp_query->is_main_query() && 'edit' === $screen->base && !current_user_can('edit_others_posts') ){
            $wp_query->set('author', get_current_user_id());
        }
    });

    /**
     * アドミンバーをカスタマイズ
     */
    add_action('admin_bar_menu', function( WP_Admin_Bar &$wp_admin_bar){
        $wp_admin_bar->remove_menu('wp-logo');
    }, 10000);

}

/**
 * Theme My Loginにno cache headersを吐かせる
 */
add_action('login_init', function(){
	nocache_headers();
});

/**
 * ツールボックスからPress Thisを消す
 *
 * @action tool_box
 */
add_action('tool_box', function(){
    echo <<<HTML
<script>
jQuery(document).ready(function($){
    $('.wrap .tool-box:eq(0)', '#wpbody-content').remove();
});
</script>
HTML;

});

/**
 * 画像ファイルを表示していたページにおける後方置換
 * @param array $atts
 * @param string $content
 * @return string 
 */
add_shortcode('file', function($atts, $content = ''){
	extract(shortcode_atts(array(
		'href' => ''
	), $atts));
	return '<p class="center"><a target="_blank" href="'.$href.'" class="button flash-button">フルサイズで表示</a></p>';
});


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
 * コメントカウント数をコメントに限定
 */
add_filter( 'get_comments_number', function($count, $post_id){
	if( !is_admin() && ($post = get_post($post_id)) ){
		/** @var wpdb $wpdb */
		global $wpdb;
		$query = <<<SQL
			SELECT COUNT(comment_ID) FROM {$wpdb->comments}
			WHERE comment_post_ID = %d
			  AND comment_approved = '1'
			  AND comment_type = ''
SQL;
		$count = (int) $wpdb->get_var($wpdb->prepare($query, $post_id));
	}
	return $count;
}, 10, 2);



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
 * ALO NewsLetterが表示されないので、修正。
 */
add_filter('alo_easymail_register_newsletter_args', function($args){
	unset($args['capabilities']);
	$args['capability_type'] = 'page';
	return $args;
});