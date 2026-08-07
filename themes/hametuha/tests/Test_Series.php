<?php
/**
 * シリーズの機能をテストする
 *
 * @package Hametuha
 */

/**
 * Sample test case.
 */
class Test_Series extends WP_UnitTestCase {

	/**
	 * @var \Hametuha\Model\Series
	 */
	protected $series;

	/**
	 * Set up
	 */
	public function setUp(): void {
		parent::setUp();
		$this->series = \Hametuha\Model\Series::get_instance();
	}

	/**
	 * Twitter/X URLの判定をテスト
	 */
	public function test_twitter() {
		// 有効なTwitter/X URL
		$valid_urls = [
			'https://twitter.com/username/status/123456',
			'https://x.com/username/status/123456',
			'https://www.twitter.com/username/status/123456',
			'https://www.x.com/username/status/123456',
			'https://twitter.com/username/status/123456/',
			'http://twitter.com/username/status/987654',
			'http://x.com/username/status/987654',
		];

		foreach ( $valid_urls as $url ) {
			$this->assertTrue(
				$this->series->is_service( $url, 'twitter' ),
				"URL should be recognized as Twitter/X: {$url}"
			);
		}

		// 無効なTwitter/X URL
		$invalid_urls = [
			'https://twitter.com/username',
			'https://twitter.com',
			'https://x.com/username',
			'https://facebook.com/post/123456',
			'https://example.com',
			'',
			'not-a-url',
		];

		foreach ( $invalid_urls as $url ) {
			$this->assertFalse(
				$this->series->is_service( $url, 'twitter' ),
				"URL should NOT be recognized as Twitter/X: {$url}"
			);
		}
	}

	/**
	 * Amazon URLの判定をテスト
	 */
	public function test_amazon() {
		// 有効なAmazon URL
		$valid_urls = [
			'https://www.amazon.co.jp/dp/XXXXX',
			'https://amazon.co.jp/dp/XXXXX',
			'https://www.amazon.com/dp/XXXXX',
			'https://amazon.com/dp/XXXXX',
			'http://www.amazon.co.jp/gp/product/XXXXX',
			'https://smile.amazon.com/dp/XXXXX',
			'https://www.amazon.co.jp/gp/product/XXXXX/ref=nosim?tag=hametuha-22',
		];

		foreach ( $valid_urls as $url ) {
			$this->assertTrue(
				$this->series->is_service( $url, 'amazon' ),
				"URL should be recognized as Amazon: {$url}"
			);
		}

		// 無効なAmazon URL
		$invalid_urls = [
			'https://google.com',
			'https://rakuten.co.jp',
			'https://example.com',
			'',
			'not-a-url',
		];

		foreach ( $invalid_urls as $url ) {
			$this->assertFalse(
				$this->series->is_service( $url, 'amazon' ),
				"URL should NOT be recognized as Amazon: {$url}"
			);
		}
	}

	/**
	 * 未定義のサービスをテスト
	 */
	public function test_unknown_service() {
		$urls = [
			'https://twitter.com/username/status/123456',
			'https://amazon.co.jp/dp/XXXXX',
			'https://example.com',
		];

		foreach ( $urls as $url ) {
			$this->assertFalse(
				$this->series->is_service( $url, 'unknown_service' ),
				"Unknown service should always return false: {$url}"
			);
		}
	}

	/**
	 * 序文・あとがきのタイトルが上書きできることをテスト
	 *
	 * @dataProvider custom_title_provider
	 *
	 * @param string $method   Series モデルのメソッド名。
	 * @param string $meta_key 上書き用のメタキー。
	 * @param string $default  未設定時のデフォルト。
	 * @param string $custom   上書きする値。
	 */
	public function test_custom_title( $method, $meta_key, $default, $custom ) {
		// publish に遷移させると計測フック（cookie-tasting プラグイン依存）が走るため draft で作る。
		$series_id = $this->factory->post->create( [
			'post_type'   => 'series',
			'post_status' => 'draft',
		] );

		// 未設定ならデフォルト。
		$this->assertSame( $default, $this->series->$method( $series_id ) );

		// 空白のみでもデフォルト（全角スペースを含む）。
		update_post_meta( $series_id, $meta_key, " \n　" );
		$this->assertSame( $default, $this->series->$method( $series_id ) );

		// 入力があれば上書き。
		update_post_meta( $series_id, $meta_key, $custom );
		$this->assertSame( $custom, $this->series->$method( $series_id ) );

		// 前後の空白は除去される。
		update_post_meta( $series_id, $meta_key, " {$custom}　" );
		$this->assertSame( $custom, $this->series->$method( $series_id ) );
	}

	/**
	 * @return array
	 */
	public function custom_title_provider() {
		return [
			'あとがき' => [ 'get_afterword_title', '_afterword_title', 'あとがき', '解説' ],
			'序文'     => [ 'get_preface_title', '_preface_title', 'はじめに', '献辞' ],
		];
	}

	/**
	 * 単巻書籍フラグをテスト
	 */
	public function test_is_standalone() {
		// publish に遷移させると計測フック（cookie-tasting プラグイン依存）が走るため draft で作る。
		$series_id = $this->factory->post->create( [
			'post_type'   => 'series',
			'post_status' => 'draft',
		] );

		// 未設定なら連載扱い。子が0件でも単巻書籍とはみなさない。
		$this->assertFalse( $this->series->is_standalone( $series_id ) );

		update_post_meta( $series_id, '_standalone_book', 1 );
		$this->assertTrue( $this->series->is_standalone( $series_id ) );

		// 明示的に0を入れた場合も連載扱い。
		update_post_meta( $series_id, '_standalone_book', 0 );
		$this->assertFalse( $this->series->is_standalone( $series_id ) );
	}

	/**
	 * 内容紹介の取得をテスト
	 */
	public function test_get_book_description() {
		$series_id = $this->factory->post->create( [
			'post_type'   => 'series',
			'post_status' => 'draft',
		] );

		// 未設定なら空文字列。
		$this->assertSame( '', $this->series->get_book_description( $series_id ) );

		// 空白のみでも空文字列として扱う（セクションを出さない判定に使うため）。
		update_post_meta( $series_id, '_book_description', "  \n " );
		$this->assertSame( '', $this->series->get_book_description( $series_id ) );

		$description = '<ol><li>吾輩は猫である</li></ol>';
		update_post_meta( $series_id, '_book_description', $description );
		$this->assertSame( $description, $this->series->get_book_description( $series_id ) );
	}

	/**
	 * テンプレートタグが series と子投稿の両方で解決することをテスト
	 */
	public function test_standalone_template_tag() {
		$series_id = $this->factory->post->create( [
			'post_type'   => 'series',
			'post_status' => 'draft',
		] );
		$child_id  = $this->factory->post->create( [
			'post_type'   => 'post',
			'post_status' => 'draft',
			'post_parent' => $series_id,
		] );
		$orphan_id = $this->factory->post->create( [
			'post_type'   => 'post',
			'post_status' => 'draft',
		] );

		update_post_meta( $series_id, '_standalone_book', 1 );

		$this->assertTrue( hametuha_is_standalone_book( $series_id ) );
		// 子投稿からは親を辿る。
		$this->assertTrue( hametuha_is_standalone_book( $child_id ) );
		// 親を持たない投稿は常に false。
		$this->assertFalse( hametuha_is_standalone_book( $orphan_id ) );
	}

	/**
	 * 一覧から「子投稿のない連載」だけが除外されることをテスト
	 *
	 * hooks/series.php の posts_join フィルターの回帰テスト。SQL を直接書いているため
	 * 壊れても表示を見るまで気づきにくい。
	 */
	public function test_archive_excludes_empty_series_but_keeps_standalone() {
		$with_child = $this->create_published_series( '子のある連載' );
		$this->create_published_post( '子作品', $with_child );

		$empty = $this->create_published_series( '空の連載' );

		$standalone = $this->create_published_series( '単巻書籍' );
		update_post_meta( $standalone, '_standalone_book', 1 );

		$this->go_to( home_url( '/?post_type=series' ) );
		$found = wp_list_pluck( $GLOBALS['wp_query']->posts, 'ID' );

		$this->assertContains( $with_child, $found, '子投稿のある連載は一覧に出る' );
		$this->assertContains( $standalone, $found, '単巻書籍は子がなくても一覧に出る' );
		$this->assertNotContains( $empty, $found, '空の連載は従来どおり一覧に出ない' );
	}

	/**
	 * 公開済みの series を作る
	 *
	 * publish に遷移させると計測フック（cookie-tasting プラグイン依存）が走るため、
	 * draft で作ってから DB を直接書き換える。
	 *
	 * @param string $title タイトル。
	 * @return int
	 */
	protected function create_published_series( $title ) {
		return $this->create_published( $title, 'series', 0 );
	}

	/**
	 * 公開済みの投稿を作る
	 *
	 * @param string $title  タイトル。
	 * @param int    $parent 親のseries ID。
	 * @return int
	 */
	protected function create_published_post( $title, $parent ) {
		return $this->create_published( $title, 'post', $parent );
	}

	/**
	 * @param string $title     タイトル。
	 * @param string $post_type 投稿タイプ。
	 * @param int    $parent    親ID。
	 * @return int
	 */
	protected function create_published( $title, $post_type, $parent ) {
		global $wpdb;
		$post_id = $this->factory->post->create( [
			'post_type'   => $post_type,
			'post_status' => 'draft',
			'post_title'  => $title,
			'post_parent' => $parent,
		] );
		$wpdb->update( $wpdb->posts, [ 'post_status' => 'publish' ], [ 'ID' => $post_id ] );
		clean_post_cache( $post_id );

		return $post_id;
	}

	/**
	 * 空文字列やnullのテスト
	 */
	public function test_empty_values() {
		// 空文字列
		$this->assertFalse( $this->series->is_service( '', 'twitter' ) );
		$this->assertFalse( $this->series->is_service( '', 'amazon' ) );

		// null（PHPの型強制でnullは空文字列として扱われる）
		$this->assertFalse( $this->series->is_service( null, 'twitter' ) );
		$this->assertFalse( $this->series->is_service( null, 'amazon' ) );
	}
}
