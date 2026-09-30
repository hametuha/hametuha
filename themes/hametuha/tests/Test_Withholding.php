<?php
/**
 * 源泉徴収一覧 API のテスト
 *
 * @package Hametuha
 */

use Hametuha\Sharee\Master\Address;
use Hametuha\Sharee\Models\RevenueModel;

/**
 * GET /hametuha/v1/sales/withholding/{year}/{month}
 */
class Test_Withholding extends WP_UnitTestCase {

	/**
	 * @var int
	 */
	protected $author_id;

	/**
	 * @var int
	 */
	protected $admin_id;

	public function set_up() {
		parent::set_up();
		// dbDelta は一時テーブルとの差分比較で落ちるため、スキーマを直接流す。
		global $wpdb;
		$wpdb->query( str_replace( 'CREATE TABLE', 'CREATE TABLE IF NOT EXISTS', RevenueModel::get_instance()->get_tables_schema( '' ) ) );
		// rest_api_init を発火させ、テーマが登録したルートを使う。
		global $wp_rest_server;
		$wp_rest_server = null;
		rest_get_server();

		$this->admin_id  = self::factory()->user->create( [ 'role' => 'administrator' ] );
		$this->author_id = self::factory()->user->create( [
			'role'         => 'author',
			'display_name' => '筆名太郎',
		] );
		$address = new Address( $this->author_id );
		$address->update( 'name', '本名太郎' );
		$address->update( 'zip', '1000001' );
		$address->update( 'address', '東京都千代田区千代田1-1' );
	}

	public function tear_down() {
		global $wp_rest_server;
		$wp_rest_server = null;
		parent::tear_down();
	}

	/**
	 * 支払い済みの行を入れる
	 *
	 * @param int    $user_id   ユーザー
	 * @param string $fixed     支払い済みにした日時
	 * @param int    $price     金額
	 * @param int    $deducting 源泉額
	 * @param int    $status    ステータス
	 */
	protected function add_revenue( $user_id, $fixed, $price, $deducting, $status = 1 ) {
		$tax = (int) round( $price * 0.1 );
		RevenueModel::get_instance()->insert( [
			'revenue_type' => 'kdp',
			'object_id'    => $user_id,
			'price'        => $price,
			'unit'         => 1,
			'tax'          => $tax,
			'deducting'    => $deducting,
			'total'        => $price + $tax - $deducting,
			'status'       => $status,
			'description'  => '',
			'created'      => $fixed,
			'fixed'        => $fixed,
			'updated'      => $fixed,
		] );
	}

	/**
	 * API を呼ぶ
	 *
	 * @param int $year  年
	 * @param int $month 月
	 * @return WP_REST_Response
	 */
	protected function request( $year, $month ) {
		return rest_do_request( new WP_REST_Request( 'GET', sprintf( '/hametuha/v1/sales/withholding/%d/%d', $year, $month ) ) );
	}

	public function test_guest_and_author_are_denied() {
		$this->assertSame( 401, $this->request( 2026, 9 )->get_status(), '未ログインは401' );
		wp_set_current_user( $this->author_id );
		$this->assertSame( 403, $this->request( 2026, 9 )->get_status(), 'edit_users が無ければ403' );
	}

	public function test_invalid_month_is_rejected() {
		wp_set_current_user( $this->admin_id );
		$this->assertSame( 400, $this->request( 2026, 13 )->get_status() );
	}

	public function test_empty_month_returns_empty_records() {
		wp_set_current_user( $this->admin_id );
		$response = $this->request( 2026, 9 );
		$this->assertSame( 200, $response->get_status() );
		$this->assertSame( [
			'year'    => 2026,
			'month'   => 9,
			'records' => [],
		], $response->get_data() );
	}

	public function test_records_are_merged_per_user_with_last_date() {
		// 同じ月に2回支払い済みにした。
		$this->add_revenue( $this->author_id, '2026-09-10 10:00:00', 10000, 1021 );
		$this->add_revenue( $this->author_id, '2026-09-28 10:00:00', 5000, 510 );
		// 対象外：源泉なし、未払い、別の月。
		$this->add_revenue( $this->author_id, '2026-09-15 10:00:00', 3000, 0 );
		$this->add_revenue( $this->author_id, '2026-09-20 10:00:00', 7000, 714, 0 );
		$this->add_revenue( $this->author_id, '2026-10-01 10:00:00', 8000, 816 );

		wp_set_current_user( $this->admin_id );
		$records = $this->request( 2026, 9 )->get_data()['records'];
		$this->assertCount( 1, $records );
		$this->assertSame( [
			'key'          => sprintf( 'wp:202609-%d', $this->author_id ),
			'user_id'      => $this->author_id,
			'month'        => 9,
			'day'          => 28,
			'payee'        => '本名太郎',
			'label'        => '原稿料ほか',
			'before_tax'   => 15000,
			'deducting'    => 1531,
			'tax'          => 1500,
			'total'        => 14969,
			'address'      => '〒1000001 東京都千代田区千代田1-1',
			'display_name' => '筆名太郎',
		], $records[0] );
	}

	public function test_columns_match_spreadsheet_order() {
		$this->add_revenue( $this->author_id, '2026-01-05 10:00:00', 10000, 1021 );
		$records = \Hametuha\Master\Withholding::get_records( 2026, 1 );
		$this->assertSame(
			[ '01', '05', '本名太郎', '原稿料ほか', 10000, 1021, 1000, 9979, '〒1000001 東京都千代田区千代田1-1', '筆名太郎' ],
			\Hametuha\Master\Withholding::to_columns( $records[0] )
		);
	}
}
