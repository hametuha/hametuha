<?php
/**
 * 匿名ユーザーに関する処理
 *
 * @package hametuha
 */


/**
 * 匿名ユーザーのログイン名を返す
 *
 * @return string
 */
function hametuha_get_anonymous_user_login() {
	return 'anonymous-coward';
}

/**
 * 匿名ユーザーオブジェクトを返す
 *
 * @return WP_User
 */
function hametuha_get_anonymous_user() {
	$anonymous = wp_cache_get( 'hametuha_anonymous_user' );
	if ( false === $anonymous ) {
		$anonymous = get_user_by( 'login', hametuha_get_anonymous_user_login() );
		wp_cache_set( 'hametuha_anonymous_user', $anonymous );
	}
	return $anonymous;
}
