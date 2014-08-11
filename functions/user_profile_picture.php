<?php

// フックを登録する
\Hametuha\User\Profile\Picture::register_hook();

/**
 * グラバターが有効か否かを調べる
 *
 * @global wpdb $wpdb
 * @param int $user_id 
 * @return boolean
 */
function has_gravatar($user_id){
	global $wpdb;
	$mail = $wpdb->get_var($wpdb->prepare("SELECT user_email FROM {$wpdb->users} WHERE ID = %d", $user_id));
	if( $mail ){
		$hash = md5(strtolower($mail));
		$endpoint = "http://www.gravatar.com/avatar/{$hash}?s=1&d=404";
		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL => $endpoint,
			CURLOPT_HEADER => true,
			CURLOPT_NOBODY => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_USERAGENT => $_SERVER['HTTP_USER_AGENT'],
			CURLOPT_TIMEOUT => 5,
			CURLOPT_FOLLOWLOCATION => false
		));
		$header = explode("(\r\n|\r|\n){2}", curl_exec($ch), 2);
		curl_close($ch);
		return false !== strpos($header[0], "200 OK");
	}else{
		return false;
	}
}

/**
 * ユーザーがオリジナルのプロファイル画像を設定しているか否か
 *
 * @param int $user_id
 * @return boolean 
 */
function has_original_picture($user_id){
    /** @var \Hametuha\User\Profile\Picture $instance */
    $instance = \Hametuha\User\Profile\Picture::get_instance();
	return $instance->has_profile_pic($user_id);
}

