<?php
/**
 * 緊急告知のテスト
 *
 * @package Hametuha
 */

use Hametuha\Hooks\EmergencyNotice;

/**
 * src/Hametuha/Hooks/EmergencyNotice.php
 */
class Test_EmergencyNotice extends WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
		EmergencyNotice::get_instance()->register_setting();
		$this->remove_file();
	}

	public function tear_down() {
		$this->remove_file();
		parent::tear_down();
	}

	private function remove_file() {
		if ( file_exists( EmergencyNotice::path() ) ) {
			unlink( EmergencyNotice::path() );
		}
	}

	private function read_file() {
		return json_decode( file_get_contents( EmergencyNotice::path() ), true );
	}

	public function test_save_writes_file() {
		update_option( 'timezone_string', 'Asia/Tokyo' );
		update_option( EmergencyNotice::OPTION, [
			'title'   => 'メンテナンス',
			'message' => "15:00に止まります\n<b>すぐ戻ります</b>",
			'until'   => '2026-11-10T15:30',
		] );
		$json = $this->read_file();
		$this->assertSame( 'メンテナンス', $json['title'] );
		$this->assertSame( "15:00に止まります\nすぐ戻ります", $json['message'], 'HTML は落とし、改行は残す' );
		$this->assertSame( '2026-11-10T15:30:00+09:00', $json['until'], 'サイトのタイムゾーンで ISO 8601 にする' );
		$this->assertNotEmpty( $json['id'] );
	}

	public function test_empty_message_removes_file() {
		update_option( EmergencyNotice::OPTION, [ 'message' => '止まります' ] );
		$this->assertFileExists( EmergencyNotice::path() );
		$this->assertArrayNotHasKey( 'until', $this->read_file(), '空の項目は書かない' );

		update_option( EmergencyNotice::OPTION, [ 'message' => '' ] );
		$this->assertFileDoesNotExist( EmergencyNotice::path(), '本文が空なら取り下げ' );
		$this->assertSame( EmergencyNotice::defaults(), EmergencyNotice::get() );
	}

	public function test_clear_button_removes_file() {
		update_option( EmergencyNotice::OPTION, [ 'message' => '止まります' ] );
		update_option( EmergencyNotice::OPTION, [ 'message' => '止まります', 'clear' => '1' ] );
		$this->assertFileDoesNotExist( EmergencyNotice::path() );
	}

	public function test_id_changes_only_when_text_changes() {
		$notice = EmergencyNotice::get_instance();
		update_option( EmergencyNotice::OPTION, [ 'title' => 'A', 'message' => '止まります' ] );
		$id = EmergencyNotice::get()['id'];

		$this->assertSame( $id, $notice->sanitize( [ 'title' => 'A', 'message' => '止まります', 'until' => '2026-12-01T00:00' ] )['id'], '期限だけの変更では出し直さない' );

		// 既存の id とかぶらないよう、明らかに違う id を入れておく
		update_option( EmergencyNotice::OPTION, array_merge( EmergencyNotice::get(), [ 'id' => 'old' ] ) );
		$this->assertNotSame( 'old', $notice->sanitize( [ 'title' => 'A', 'message' => '15時に止まります' ] )['id'], '本文を変えたら出し直す' );
		$this->assertNotSame( 'old', $notice->sanitize( [ 'title' => 'B', 'message' => '止まります' ] )['id'], 'タイトルを変えたら出し直す' );
	}

	public function test_resave_restores_deleted_file() {
		update_option( EmergencyNotice::OPTION, [ 'message' => '止まります' ] );
		$this->remove_file();
		// 画面で何も変えずに「保存」したのと同じ
		update_option( EmergencyNotice::OPTION, EmergencyNotice::get() );
		$this->assertFileExists( EmergencyNotice::path(), '値が同じでも書き出し直す' );
	}

	public function test_invalid_until_is_ignored() {
		$this->assertSame( '', EmergencyNotice::sanitize_until( 'あした' ) );
		$this->assertSame( '', EmergencyNotice::sanitize_until( '' ) );
	}
}
