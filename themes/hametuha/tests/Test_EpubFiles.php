<?php
/**
 * ePub ファイル一覧 API の権限のテスト
 *
 * 方針：投稿者は自分が所有する作品集のファイルだけ見られる。全体の一覧は編集者のみ。
 *
 * @package Hametuha
 */

/**
 * GET /hametuha/v1/epub/files
 */
class Test_EpubFiles extends WP_UnitTestCase {

	/**
	 * @var int
	 */
	protected $author_id;

	/**
	 * @var int
	 */
	protected $own_series;

	/**
	 * @var int
	 */
	protected $others_series;

	public function set_up() {
		parent::set_up();
		// rest_api_init を発火させ、テーマが登録したルートを使う。
		global $wp_rest_server, $wpdb;
		$wp_rest_server = null;
		rest_get_server();
		// テスト環境には compiled_files テーブルが無い。ここで見たいのは権限だけ。
		$wpdb->suppress_errors( true );

		$this->author_id     = self::factory()->user->create( [ 'role' => 'author' ] );
		$this->own_series    = self::factory()->post->create( [
			'post_type'   => 'series',
			'post_status' => 'draft',
			'post_author' => $this->author_id,
		] );
		$this->others_series = self::factory()->post->create( [
			'post_type'   => 'series',
			'post_status' => 'draft',
			'post_author' => self::factory()->user->create( [ 'role' => 'author' ] ),
		] );
	}

	public function tear_down() {
		global $wp_rest_server, $wpdb;
		$wp_rest_server = null;
		$wpdb->suppress_errors( false );
		parent::tear_down();
	}

	/**
	 * 一覧を取得する
	 *
	 * @param array $params クエリ文字列（値は文字列で届く）
	 * @return int ステータスコード
	 */
	protected function status( $params ) {
		$request = new WP_REST_Request( 'GET', '/hametuha/v1/epub/files' );
		$request->set_query_params( array_map( 'strval', $params ) );
		return rest_do_request( $request )->get_status();
	}

	public function test_author_can_list_files_of_own_series() {
		wp_set_current_user( $this->author_id );
		$this->assertSame( 200, $this->status( [ 'p' => $this->own_series ] ), '自分の作品集を指定' );
		$this->assertSame( 200, $this->status( [ 'author' => $this->author_id ] ), '自分のIDを指定' );
	}

	public function test_author_cannot_list_others_files() {
		wp_set_current_user( $this->author_id );
		$this->assertSame( 403, $this->status( [] ), '全体の一覧' );
		$this->assertSame( 403, $this->status( [ 'p' => $this->others_series ] ), '他人の作品集' );
		$this->assertSame( 403, $this->status( [ 'author' => get_post( $this->others_series )->post_author ] ), '他人のID' );
	}

	public function test_editor_can_list_all_files() {
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'editor' ] ) );
		$this->assertSame( 200, $this->status( [] ) );
	}
}
