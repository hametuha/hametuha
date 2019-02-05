<?php
/**
 * 掲示板を作成する
 * @since 3.2
 */

/**
 * 破滅派BBSにおけるエラーメッセージ格納コンテナ
 * @var array 
 */
global $_hametuha_thread_error;
$_hametuha_thread_error= array();


/**
 * 匿名ユーザーのログイン名を返す
 * @return string 
 */
function get_anonymous_user_login(){
	return 'anonymous-coward';
}

/**
 * 匿名ユーザーオブジェクトを返す
 * @uses WP_Cache
 * @return WP_User
 */
function get_anonymous_user(){
	$anonymous = wp_cache_get('hametuha_anonymous_user');
	if( false === $anonymous ){
		$anonymous = get_user_by('login', get_anonymous_user_login());
		wp_cache_set('hametuha_anonymous_user', $anonymous);
	}
	return $anonymous;
}



/**
 * 無記名のコメント
 * @global array $_hametuha_thread_error
 */
function _hametuha_anonymous_comment(){
	if( is_singular('thread') && isset($_REQUEST['_anonymous_comment_nonce']) && wp_verify_nonce($_REQUEST['_anonymous_comment_nonce'], 'thread_anonymous_reply') ){
		global $_hametuha_thread_error;
		//キャプチャのチェック
		if( !WPametu::validate_recaptcha() ){
			$_hametuha_thread_error[] = 'キャプチャの文字がまちがっています。';
		}
		//コメントのチェック
		if( !isset($_REQUEST['comment']) || empty($_REQUEST['comment']) ){
			$_hametuha_thread_error[] = 'コメント本文が入力されていません。';
		}
		if( empty($_hametuha_thread_error) ){
			$user = get_anonymous_user();
			$result = wp_new_comment(array(
				'comment_post_ID' => get_the_ID(),
				'comment_author' => $user->display_name,
				'comment_author_email' => $user->user_email,
				'comment_author_url' => $user->user_url,
				'comment_content' => (string)$_REQUEST['comment'],
				'comment_type' => '',
				'comment_parent' => $_REQUEST['comment_parent'],
				'user_id' => $user->ID,
				'comment_author_IP' => $_SERVER['REMOTE_ADDR'],
				'comment_agent' => $_SERVER['HTTP_USER_AGENT'],
				'comment_date' => current_time('mysql'),
				'comment_approved' => 1
			));
			if( !$result ){
				$_hametuha_thread_error[] = 'コメントを保存できませんでした。時間をおいてもう一度試してみてください。';
			}
		}
	}
}
add_action('template_redirect', '_hametuha_anonymous_comment');



add_action( 'wp_footer', function() {
    static $did = false;
    if ( $did ) {
        return;
    }
	if ( is_singular( 'faq' ) || is_post_type_archive( 'faq' ) || is_tax( 'faq_cat' ) || is_page( 'help' ) ) {
		?>
		<!-- Your customer chat code -->
		<div class="fb-customerchat"
			 attribution=setup_tool
			 page_id="196103120449777"
			 theme_color="#000000"
			 logged_in_greeting="めつかれさまです。なにかお困りですか？"
			 logged_out_greeting="めつかれさまです。なにかお困りですか？">
		</div>
		<?php
	}
} );