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
		// bootstrap.php で切っているのを戻す（tear_down で元に戻る）
		add_filter( 'hametuha_two_factor_enforced', '__return_true', 11 );
	}

	public function tear_down() {
		unset( $_COOKIE[ LOGGED_IN_COOKIE ] );
		parent::tear_down();
	}

	/**
	 * ユーザーとしてログインし、セッションを作る
	 *
	 * @param int  $user_id   User ID.
	 * @param bool $two_factor 2要素認証を通ったセッションにするか。
	 * @return void
	 */
	private function login( $user_id, $two_factor ) {
		$expiration = time() + HOUR_IN_SECONDS;
		$session    = [ 'expiration' => $expiration ];
		if ( $two_factor ) {
			$session['two-factor-login'] = time();
		}
		$manager = WP_Session_Tokens::get_instance( $user_id );
		$token   = $manager->create( $expiration );
		$manager->update( $token, $session );
		$_COOKIE[ LOGGED_IN_COOKIE ] = wp_generate_auth_cookie( $user_id, $expiration, 'logged_in', $token );
		wp_set_current_user( $user_id );
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

	public function test_admin_without_verified_session_is_restricted() {
		$admin = self::factory()->user->create( [ 'role' => 'administrator' ] );
		$this->login( $admin, false );
		$this->assertFalse( current_user_can( 'manage_options' ), '2要素認証を通っていなければ管理者権限は無い' );
		$this->assertFalse( current_user_can( 'edit_posts' ) );
		$this->assertTrue( current_user_can( 'read' ), 'プロフィール画面には入れる' );
		$this->assertTrue( current_user_can( 'edit_user', $admin ), '自分の2要素認証は設定できる' );
		$this->assertTrue( current_user_can( 'administrator' ), 'ロール名は残す（hashboard がプロフィール画面の判定に使う）' );
	}

	public function test_admin_with_verified_session_has_full_caps() {
		$admin = self::factory()->user->create( [ 'role' => 'administrator' ] );
		$this->login( $admin, true );
		$this->assertTrue( current_user_can( 'manage_options' ), '2要素認証を通ったセッションなら管理者権限がある' );
	}

	public function test_other_users_caps_are_untouched() {
		$admin  = self::factory()->user->create( [ 'role' => 'administrator' ] );
		$author = self::factory()->user->create( [ 'role' => 'author' ] );
		$this->login( $author, false );
		$this->assertTrue( current_user_can( 'edit_posts' ), '管理者以外は影響を受けない' );
		$this->assertTrue( user_can( $admin, 'manage_options' ), 'ログイン中の本人以外の権限判定は変えない' );
	}

	public function test_social_login_is_rejected_for_admin() {
		$admin  = self::factory()->user->create( [ 'role' => 'administrator' ] );
		$author = self::factory()->user->create( [ 'role' => 'author' ] );
		do_action( 'gianism_before_set_login_cookie', $author, 'google' );
		$this->expectException( Exception::class );
		do_action( 'gianism_before_set_login_cookie', $admin, 'google' );
	}
}
