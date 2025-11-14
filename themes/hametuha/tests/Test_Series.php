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
