<?php
/**
 * ePub ファイル一覧 API の引数のテスト
 *
 * @package Hametuha
 */

/**
 * GET /hametuha/v1/epub/files の author
 */
class Test_EpubFiles extends WP_UnitTestCase {

	public function test_author_can_filter_by_own_id_given_as_string() {
		$author_id = self::factory()->user->create( [ 'role' => 'author' ] );
		$other_id  = self::factory()->user->create( [ 'role' => 'author' ] );
		wp_set_current_user( $author_id );

		$api = new \Hametuha\WpApi\EpubFiles();
		$ref = new ReflectionMethod( $api, 'get_arguments' );
		$ref->setAccessible( true );
		$callback = $ref->invoke( $api, 'GET' )['author']['validate_callback'];

		// クエリ文字列の値は文字列で届く
		$this->assertTrue( $callback( (string) $author_id ), '自分のIDなら絞り込める' );
		$this->assertFalse( $callback( (string) $other_id ), '他人のIDは弾く' );
	}
}
