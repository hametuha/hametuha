<?php
/**
 * 管理者・編集者の2要素認証必須化のテスト
 *
 * @feature-group two-factor
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
		$editor     = self::factory()->user->create( [ 'role' => 'editor' ] );
		$subscriber = self::factory()->user->create( [ 'role' => 'subscriber' ] );
		$this->assertEqualsCanonicalizing(
			[ 'Two_Factor_Totp', 'Two_Factor_Backup_Codes' ],
			array_keys( Two_Factor_Core::get_supported_providers_for_user( $admin ) ),
			'管理者は TOTP とバックアップコードだけ（メールは使えない）'
		);
		$this->assertSame(
			[ 'Two_Factor_Email' ],
			array_keys( Two_Factor_Core::get_supported_providers_for_user( $editor ) ),
			'編集者はメールだけ'
		);
		$this->assertSame( [], Two_Factor_Core::get_supported_providers_for_user( $subscriber ), '管理者・編集者以外には提供しない' );
	}

	public function test_editor_is_forced_to_use_email() {
		$editor = self::factory()->user->create( [ 'role' => 'editor' ] );
		$this->assertTrue( Two_Factor_Core::is_user_using_two_factor( $editor ), '設定していなくても2要素認証を求める' );
		$this->assertInstanceOf( 'Two_Factor_Email', Two_Factor_Core::get_primary_provider_for_user( $editor ), 'メールで確認する' );

		// 本人が外そうとしても（保存値を空にしても）外れない
		update_user_meta( $editor, Two_Factor_Core::ENABLED_PROVIDERS_USER_META_KEY, [] );
		$this->assertTrue( Two_Factor_Core::is_user_using_two_factor( $editor ), '本人は外せない' );
	}

	public function test_admin_is_not_forced() {
		$admin = self::factory()->user->create( [ 'role' => 'administrator' ] );
		$this->assertFalse( Two_Factor_Core::is_user_using_two_factor( $admin ), '管理者は自分で TOTP を設定する（強制しない）' );
	}

	public function test_admin_with_editor_role_follows_admin_policy() {
		$user_id = self::factory()->user->create( [ 'role' => 'editor' ] );
		( new WP_User( $user_id ) )->add_role( 'administrator' );
		$this->assertSame( 'administrator', TwoFactor::get_role( $user_id ), '管理者の方針を優先する' );
		$this->assertArrayNotHasKey( 'Two_Factor_Email', Two_Factor_Core::get_supported_providers_for_user( $user_id ), '管理者にはメールを使わせない' );
	}

	public function test_demoted_user_is_not_locked_out() {
		$user_id = self::factory()->user->create( [ 'role' => 'subscriber' ] );
		// 管理者・編集者だったころの設定が残っている
		update_user_meta( $user_id, Two_Factor_Core::ENABLED_PROVIDERS_USER_META_KEY, [ 'Two_Factor_Totp', 'Two_Factor_Email' ] );
		$this->assertFalse( Two_Factor_Core::is_user_using_two_factor( $user_id ), '降格後は2要素認証を求めない（求めると fail closed で締め出される）' );
	}

	public function test_notice_for_admin_without_two_factor() {
		$admin = self::factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $admin );
		ob_start();
		TwoFactor::get_instance()->admin_notice();
		$this->assertStringContainsString( '2要素認証が必須', ob_get_clean(), '未設定の管理者には通知する' );

		foreach ( [ 'editor', 'author' ] as $role ) {
			wp_set_current_user( self::factory()->user->create( [ 'role' => $role ] ) );
			ob_start();
			TwoFactor::get_instance()->admin_notice();
			$this->assertSame( '', ob_get_clean(), "{$role} には出さない" );
		}
	}
}
