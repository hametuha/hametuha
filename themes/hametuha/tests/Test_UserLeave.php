<?php
/**
 * 退会時の処理のテスト
 *
 * @package Hametuha
 */

/**
 * hooks/user.php の nlmg_before_leave
 */
class Test_UserLeave extends WP_UnitTestCase {

	public function test_news_is_moved_to_anonymous_user() {
		$anonymous_id = self::factory()->user->create( [ 'user_login' => hametuha_get_anonymous_user_login() ] );
		wp_cache_delete( 'hametuha_anonymous_user' );
		$user_id = self::factory()->user->create( [ 'role' => 'author' ] );
		$news_id = self::factory()->post->create( [
			'post_type'   => 'news',
			'post_status' => 'draft',
			'post_author' => $user_id,
		] );
		$post_id = self::factory()->post->create( [
			'post_type'   => 'post',
			'post_status' => 'draft',
			'post_author' => $user_id,
		] );

		do_action( 'nlmg_before_leave', $user_id );
		clean_post_cache( $news_id );
		clean_post_cache( $post_id );

		$this->assertSame( (string) $anonymous_id, get_post( $news_id )->post_author, 'ニュースは匿名ユーザーに移る' );
		$this->assertSame( (string) $user_id, get_post( $post_id )->post_author, '作品はそのまま' );
	}
}
