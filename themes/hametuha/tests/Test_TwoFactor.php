<?php
/**
 * 管理者の2要素認証必須化のテスト
 *
 * @package Hametuha
 */

use Hametuha\Hooks\TwoFactor;

/**
 * src/Hametuha/Hooks/TwoFactor.php
 */
class Test_TwoFactor extends WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
		if ( ! class_exists( 'Two_Factor_Core' ) ) {
			$this->markTestSkipped( 'Two Factor プラグインが無い' );
		}
	}

	public function test_providers() {
		$admin      = self::factory()->user->create( [ 'role' => 'administrator' ] );
		$subscriber = self::factory()->user->create( [ 'role' => 'subscriber' ] );
		$this->assertEqualsCanonicalizing(
			TwoFactor::PROVIDERS,
			array_keys( Two_Factor_Core::get_supported_providers_for_user( $admin ) ),
			'管理者は TOTP とバックアップコードだけ（メールは使えない）'
		);
		$this->assertSame( [], Two_Factor_Core::get_supported_providers_for_user( $subscriber ), '管理者以外には提供しない' );
	}

	public function test_demoted_user_is_not_locked_out() {
		$user_id = self::factory()->user->create( [ 'role' => 'subscriber' ] );
		// 管理者だったころの設定が残っている
		update_user_meta( $user_id, Two_Factor_Core::ENABLED_PROVIDERS_USER_META_KEY, [ 'Two_Factor_Totp' ] );
		$this->assertFalse( Two_Factor_Core::is_user_using_two_factor( $user_id ), '降格後は2要素認証を求めない（求めると fail closed で締め出される）' );
	}

	public function test_notice_for_admin_without_two_factor() {
		$admin = self::factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $admin );
		ob_start();
		TwoFactor::get_instance()->admin_notice();
		$this->assertStringContainsString( '2要素認証が必須', ob_get_clean(), '未設定の管理者には通知する' );

		$author = self::factory()->user->create( [ 'role' => 'author' ] );
		wp_set_current_user( $author );
		ob_start();
		TwoFactor::get_instance()->admin_notice();
		$this->assertSame( '', ob_get_clean(), '管理者以外には出さない' );
	}
}
