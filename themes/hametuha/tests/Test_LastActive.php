<?php
/**
 * 最終利用日の記録のテスト
 *
 * @package Hametuha
 */

use Hametuha\Hooks\LastActive;

/**
 * src/Hametuha/Hooks/LastActive.php
 */
class Test_LastActive extends WP_UnitTestCase {

	public function test_no_record_returns_zero() {
		$user_id = self::factory()->user->create();
		$this->assertSame( 0, LastActive::get( $user_id ), '記録がなければ 0（推測で埋めない）' );
	}

	public function test_record_once_a_day() {
		$user_id = self::factory()->user->create();
		$hook    = LastActive::get_instance();
		// サイトのタイムゾーンで同じ日の朝と夜
		$morning = ( new DateTimeImmutable( '2026-10-07 08:00:00', wp_timezone() ) )->getTimestamp();
		$night   = ( new DateTimeImmutable( '2026-10-07 23:30:00', wp_timezone() ) )->getTimestamp();
		$next    = ( new DateTimeImmutable( '2026-10-08 00:10:00', wp_timezone() ) )->getTimestamp();

		$this->assertTrue( $hook->record( $user_id, $morning ), '初回は書き込む' );
		$this->assertFalse( $hook->record( $user_id, $night ), '同じ日は書き込まない' );
		$this->assertSame( $morning, LastActive::get( $user_id ), '同じ日の2回目で値は変わらない' );
		$this->assertTrue( $hook->record( $user_id, $next ), '日付が変われば書き込む' );
		$this->assertSame( $next, LastActive::get( $user_id ) );
	}

	public function test_login_records() {
		$user = self::factory()->user->create_and_get();
		// wp_login には cookie-tasting 依存の別処理がぶら下がっているので、直接呼ぶ
		$this->assertNotFalse( has_action( 'wp_login', [ LastActive::get_instance(), 'record_login' ] ), 'wp_login にフックされている' );
		LastActive::get_instance()->record_login( $user->user_login, $user );
		$this->assertGreaterThan( 0, LastActive::get( $user->ID ), 'ログインで記録される' );
	}

	public function test_current_user_records_on_init() {
		$user_id = self::factory()->user->create();
		wp_set_current_user( $user_id );
		LastActive::get_instance()->record_current_user();
		$this->assertGreaterThan( 0, LastActive::get( $user_id ), 'ログイン中の利用で記録される' );

		wp_set_current_user( 0 );
		// 未ログインでは何も起きない（エラーにならない）
		LastActive::get_instance()->record_current_user();
	}

	public function test_sort_keeps_users_without_record() {
		set_current_screen( 'users' );
		$old     = self::factory()->user->create();
		$recent  = self::factory()->user->create();
		$unknown = self::factory()->user->create();
		update_user_meta( $old, LastActive::META_KEY, 1000 );
		update_user_meta( $recent, LastActive::META_KEY, 2000 );
		// 別の数値メタを持っていても、それで並んではいけない
		update_user_meta( $unknown, 'work_count', 99999 );

		$ids = ( new WP_User_Query( [
			'orderby' => LastActive::META_KEY,
			'order'   => 'DESC',
			'include' => [ $old, $recent, $unknown ],
			'fields'  => 'ID',
		] ) )->get_results();
		$ids = array_map( 'intval', $ids );

		$this->assertCount( 3, $ids, '記録のないユーザーも一覧から消えない' );
		$this->assertSame( [ $recent, $old, $unknown ], $ids, '新しい順に並び、記録なしは最後' );
		set_current_screen( 'front' );
	}
}
