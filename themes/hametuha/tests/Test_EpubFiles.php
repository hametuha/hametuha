<?php
/**
 * ePub ファイル一覧 API の権限のテスト
 *
 * @package Hametuha
 */

/**
 * GET /hametuha/v1/epub/files
 */
class Test_EpubFiles extends WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
		// rest_api_init を発火させ、テーマが登録したルートを使う。
		global $wp_rest_server;
		$wp_rest_server = null;
		rest_get_server();
	}

	public function tear_down() {
		global $wp_rest_server;
		$wp_rest_server = null;
		parent::tear_down();
	}

	public function test_author_cannot_list_files_even_with_own_id() {
		$author_id = self::factory()->user->create( [ 'role' => 'author' ] );
		wp_set_current_user( $author_id );
		$this->assertFalse( ( new \Hametuha\WpApi\EpubFiles() )->permission_callback( new WP_REST_Request() ), '投稿者には権限が無い' );
		// 引数の検証が権限チェックより先に走るので、400か403のどちらかになる。
		$request = new WP_REST_Request( 'GET', '/hametuha/v1/epub/files' );
		$request->set_param( 'author', (string) $author_id );
		$this->assertTrue( rest_do_request( $request )->is_error(), '自分のIDを指定しても一覧は取れない' );
	}

	public function test_editor_has_permission() {
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'editor' ] ) );
		$this->assertTrue( ( new \Hametuha\WpApi\EpubFiles() )->permission_callback( new WP_REST_Request() ) );
	}
}
