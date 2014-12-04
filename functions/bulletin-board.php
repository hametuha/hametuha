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
 * 破滅派掲示板用のスレッドを追加する
 *
 * @action init
 */
add_action('init', function(){
	//投稿タイプthreadを登録
	register_post_type('thread', [
		'label' => 'スレッド',
		'description' => '破滅派BBSは参加者達が意見交換をする場所です。積極的にご参加ください。匿名での投稿もできます。',
		'public' => true,
		'menu_icon' => 'dashicons-feedback',
		'supports' => array('title', 'editor', 'author', 'comments'),
		'has_archive' => true,
		'capability_type' => 'post',
		'rewrite' => array('slug' => 'thread'),
		'show_ui' => current_user_can('edit_others_posts'),
		'can_export' => false
	]);
	//スレッドのカテゴリーを登録
	register_taxonomy('topic', ['thread'], [
		'hierarchical' => false,
		'show_ui' => current_user_can('edit_others_posts'),
		'query_var' => true,
		'rewrite' => ['slug' => 'topic'],
        'label' => 'トピック'
    ]);
});



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
 * スレッド作成フォームを受け取る
 *
 * @global array $_hametuha_thread_error 
 * @global wpdb $wpdb
 */
function _hametuha_thread_add(){
	if( is_post_type_archive('thread') ){
		nocache_headers();
		/** @var \WPametu\Http\Input $input */
		$input = \WPametu\Http\Input::get_instance();
		if( $input->verify_nonce('hametuha_add_thread') ) {
			global $_hametuha_thread_error, $wpdb;
			//匿名かログイン済みかを問わず、ユーザー情報をチェックする
			if ( is_user_logged_in() ) {
				$user = ( $input->post('anonymous') ) ? get_anonymous_user() : get_userdata( get_current_user_id() );
			} else {
				//キャプチャをチェックする
				if ( wpametu()->recaptcha->validate() ) {
					$user = get_anonymous_user();
				} else {
					$_hametuha_thread_error['recaptcha'] = 'reCaptchaがエラーを返しました。';
				}
			}
			// タイトルの空白・重複チェック
			if ( !$input->post('thread_title') ) {
				$_hametuha_thread_error['thread_title'] = 'タイトルが入力されていません';
			} elseif ( $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'thread' AND post_title = %s", $input->post('thread_title') ) ) ) {
				$_hametuha_thread_error['thread_title'] = '同名のスレッドがすでに存在します。別のタイトルにしてください。';
			}
			// トピック
			$topic_id = $input->post('topic_id');
			if ( !$topic_id || !is_numeric($topic_id) || !term_exists( intval($topic_id), 'topic' ) ) {
				$_hametuha_thread_error['topic_id'] = 'トピックは必ず選択してください。';
			}
			// コンテンツ
			$content = strip_tags( (string) $input->post('thread_content'));
			if ( empty( $_hametuha_thread_error ) ) {
				//エラーがないので登録
				$result = wp_insert_post( array(
					'post_title'   => (string) $input->post('thread_title'),
					'post_content' => $content ?: '詳細は入力されていません。',
					'post_author'  => $user->ID,
					'post_type'    => 'thread',
					'post_status'  => 'publish'
				), true );
				if ( is_wp_error( $result ) ) {
					$_hametuha_thread_error['add'] = 'スレッドを保存できませんでした。もう一度やりなおすか、時間をおいてお試しください。';
				} else {
					// トピックを紐付け
					wp_set_object_terms( $result, array( intval( $topic_id ) ), 'topic' );
					// 公開アクション
					do_action( 'transition_post_status', 'publish', 'post-new', get_post( $result ) );
					wp_redirect( get_permalink( $result ) );
					die();
				}
			}
		}
	}
}
add_action('template_redirect', '_hametuha_thread_add');



/**
 * スレッド編集アクション 
 * @global array $_hametuha_thread_error 
 * @global wpdb $wpdb
 */
function _hametuha_thread_edit(){
	if( is_singular('thread') && isset($_REQUEST['_wpnonce'], $_GET['action']) ){
        nocache_headers();
        if(
            $_GET['action'] == 'edit'
            &&
            wp_verify_nonce($_REQUEST['_wpnonce'], 'hametuha_thread_edit')
        ){
            global $_hametuha_thread_error, $wpdb;
            //タイトルの空白・重複チェック
            if( empty($_REQUEST['thread_title']) ){
                $_hametuha_thread_error['thread_title'] = 'タイトルが入力されていません';
            }elseif($wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_type = 'thread' AND post_title = %s AND ID != %d", $_REQUEST['thread_title'], get_the_ID()))){
                $_hametuha_thread_error['thread_title'] = '同名のスレッドがすでに存在します。別のタイトルにしてください。';
            }
            //トピック
            if( !isset($_REQUEST['topic_id']) || empty($_REQUEST['topic_id']) || !term_exists($_REQUEST['topic_id'], 'topic') ){
                $_hametuha_thread_error['topic_id'] = 'トピックは必ず選択してください。';
            }
            if( empty($_hametuha_thread_error) ){
                //エラーがないので更新
                $result = wp_update_post(array(
                    'ID' => get_the_ID(),
                    'post_title' => (string)$_REQUEST['thread_title'],
                    'post_content' => (empty($_REQUEST['thread_content'])) ? '詳細は入力されていません。' : strip_tags((string)$_REQUEST['thread_content']),
                ));
                if(!$result){
                    $_hametuha_thread_error['add'] = 'スレッドを更新できませんでした。もう一度やりなおすか、時間をおいてお試しください。';
                }else{
                    wp_set_object_terms(get_the_ID(), array(intval($_REQUEST['topic_id'])), 'topic');
                    global $wp_query;
                    query_posts($wp_query->query_vars);
                }
            }
        }
	}
}
add_action('template_redirect', '_hametuha_thread_edit');

/**
 * スレッド削除アクション 
 */
function _hametuha_thread_delete(){
	if(
		is_singular('thread')
			&&
		isset($_REQUEST['_wpnonce'], $_REQUEST['action'])
			&&
		$_REQUEST['action'] == 'delete'
			&&
		wp_verify_nonce($_REQUEST['_wpnonce'], 'hametuha_thread_delete')
			&&
		is_user_logged_in()
			&&
		current_user_can('edit_post', get_the_ID())
	){
		wp_delete_post(get_the_ID());
        wp_redirect(get_post_type_archive_link('thread'));
		die();
	}
}
add_action('template_redirect', '_hametuha_thread_delete');



/**
 * 無記名のコメント
 * @global array $_hametuha_thread_error
 */
function _hametuha_anonymous_comment(){
	if( is_singular('thread') && isset($_REQUEST['_anonymous_comment_nonce']) && wp_verify_nonce($_REQUEST['_anonymous_comment_nonce'], 'thread_anonymous_reply') ){
		global $_hametuha_thread_error;
		//キャプチャのチェック
		if( !wpametu()->recaptcha->validate() ){
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


/**
 * コメントに返信があった場合、メールを送信する
 * @global wpdb $wpdb
 * @param int $comment_id
 * @param object $comment 
 */
function _hametuha_comment_reply($comment_id, $comment){
	global $wpdb;
	//返信コメントか否か、投稿タイプがスレッドか否かをチェック
	if($comment->comment_parent > 0 && $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE ID = %d AND post_type = 'thread' LIMIT 1", $comment->comment_post_ID))){
		//親コメントのユーザーが匿名でないかをチェック
		$anonymous = get_anonymous_user();
		$parent_comment = $wpdb->get_row($wpdb->prepare("SELECT comment_ID FROM {$wpdb->comments} WHERE comment_ID = %d", $comment->comment_parent));
		if( $parent_comment->user_id > 0 && $parent_comment->user_id != $anonymous->ID ){
			$url = get_permalink($comment->comment_post_ID).'#respond';
			$message = <<<EOS
{$parent_comment->comment_author} 様



あなたのコメントに返信がありました。

-----------------
{$comment->comment_author} より

{$comment->comment_content}

-----------------

このコメントは以下のURLでご覧になれます。
{$url}


----------------
破滅派 | オンライン文芸誌
http://hametuha.com

EOS;
			$headers = "From: 破滅派 <info@hametuha.com>\r\nReply-to: info@hametuha.com\r\n";
			wp_mail(
				$parent_comment->comment_author_email,
				'[破滅派] あなたのコメントに返信がありました',
				$message,
				$headers
			);
		}
	}
}
add_action('wp_insert_comment', '_hametuha_comment_reply', 10, 2);



/**
 * スレッドのエラー情報を取得する
 * @global array $_hametuha_thread_error
 * @param string $key
 * @return string
 */
function get_thread_error( $key = null ){
	global $_hametuha_thread_error;
	if( $key ){
		if(isset($_hametuha_thread_error[$key])){
			return $_hametuha_thread_error[$key];
		}else{
			return false;
		}
	}else{
		return !empty($_hametuha_thread_error);
	}
}

/**
 * スレッドエラーが存在する場合は表示する
 * @global array $_hametuha_thread_error 
 */
function show_thread_error(){
	global $_hametuha_thread_error;
	if(!empty($_hametuha_thread_error)){
		$string = array();
		foreach($_hametuha_thread_error as $err){
			$string[] = '・'.$err;
		}
		echo '<p class="alert alert-danger">'.implode('<br />', $string).'</p>';
	}
}

/**
 * ユーザーが作成したスレッドの数を返す
 * @global wpdb $wpdb
 * @param int $user_id
 * @return int 
 */
function get_author_thread_count($user_id){
	global $wpdb;
	$sql = <<<EOS
		SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_author = %d AND post_type = %s AND post_status = 'publish'
EOS;
	return (int)$wpdb->get_var($wpdb->prepare($sql, $user_id, 'thread'));
}

/**
 * 投稿が最近コメントされたか否か
 * @param int $offset 初期値は7
 * @param object $post
 * @return boolean
 */
function recently_commented($offset = 7, $post = null){
	$latest_date = get_latest_comment_date($post);
	if(!$latest_date){
		return false;
	}
	return (boolean)((time() - strtotime($latest_date)) < 60 * 60 * 24 * $offset);
}

/**
 * 最新コメントの日付を取得する
 * @global wpdb $wpdb
 * @param object $post
 * @return string
 */
function get_latest_comment_date($post = null){
	global $wpdb;
	$post = get_post($post);
	$sql = <<<EOS
		SELECT comment_date FROM {$wpdb->comments}
		WHERE comment_post_ID = %d
		LIMIT 1
EOS;
	return $wpdb->get_var($wpdb->prepare($sql, $post->ID));
}

/**
 * ユーザーが投稿したスレッドのレス数を返す
 * @global wpdb $wpdb
 * @param int $user_id
 * @return int 
 */
function get_author_response_count($user_id){
	global $wpdb;
	$sql = <<<EOS
		SELECT COUNT(comment_ID) FROM {$wpdb->comments} AS c
		INNER JOIN {$wpdb->posts} AS p
		ON c.comment_post_ID = p.ID
		WHERE p.post_type = 'thread' AND c.user_id = %d
EOS;
	return (int)$wpdb->get_var($wpdb->prepare($sql, $user_id));
}

/**
 * コメント投稿にログインが必須か否かをフィルタリング
 * @param boolean $option
 * @return boolean
 */
function _hametuha_bbs_reply_link($option){
	if( is_singular('thread') ){
		return 0;
	}else{
		return $option;
	}
}
add_filter('option_comment_registration', '_hametuha_bbs_reply_link');
