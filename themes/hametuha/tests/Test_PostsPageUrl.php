<?php
/**
 * hametuha_posts_page_url() のテスト
 *
 * @package Hametuha
 */

/**
 * functions/utility.php
 */
class Test_PostsPageUrl extends WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
		remove_action( 'transition_post_status', [ \Hametuha\Hooks\RecordEditActions::get_instance(), 'post_transition' ], 10 );
	}

	public function test_posts_page() {
		$page_id = self::factory()->post->create( [
			'post_type' => 'page',
			'post_name' => 'latest',
		] );
		update_option( 'show_on_front', 'page' );
		update_option( 'page_for_posts', $page_id );
		$this->assertSame( get_permalink( $page_id ), hametuha_posts_page_url(), '投稿ページの URL' );
	}

	public function test_without_posts_page() {
		update_option( 'show_on_front', 'posts' );
		delete_option( 'page_for_posts' );
		$this->go_to( get_permalink( self::factory()->post->create() ) );
		$this->assertSame( home_url(), hametuha_posts_page_url(), '未設定ならトップページ（表示中の投稿ではない）' );
	}
}
