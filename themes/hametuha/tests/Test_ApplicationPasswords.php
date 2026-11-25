<?php
/**
 * アプリケーションパスワードを管理者に限定するテスト
 *
 * @feature-group two-factor
 * @package Hametuha
 */

/**
 * src/Hametuha/Hooks/ApplicationPasswords.php
 */
class Test_ApplicationPasswords extends WP_UnitTestCase {

	/**
	 * REST で自分のアプリケーションパスワードを作る
	 *
	 * @param int $user_id User ID.
	 * @return WP_REST_Response
	 */
	private function create_via_rest( $user_id ) {
		wp_set_current_user( $user_id );
		$request = new WP_REST_Request( 'POST', '/wp/v2/users/me/application-passwords' );
		$request->set_body_params( [ 'name' => 'test' ] );
		return rest_get_server()->dispatch( $request );
	}

	public function set_up() {
		parent::set_up();
		// テスト環境は HTTPS ではないので、ローカル環境と同じく使える状態にする
		add_filter( 'wp_is_application_passwords_available', '__return_true' );
		do_action( 'rest_api_init', rest_get_server() );
	}

	public function test_admin_can_create() {
		$admin    = self::factory()->user->create( [ 'role' => 'administrator' ] );
		$response = $this->create_via_rest( $admin );
		$this->assertSame( 201, $response->get_status(), '管理者は発行できる' );
	}

	/**
	 * @dataProvider provide_non_admin_roles
	 */
	public function test_non_admin_cannot_create( $role ) {
		$user     = self::factory()->user->create( [ 'role' => $role ] );
		$response = $this->create_via_rest( $user );
		$this->assertSame( 'application_passwords_disabled_for_user', $response->as_error()->get_error_code(), "{$role} は発行できない" );
		$this->assertSame( [], WP_Application_Passwords::get_user_application_passwords( $user ) );
	}

	public function provide_non_admin_roles() {
		return [
			[ 'editor' ],
			[ 'author' ],
			[ 'contributor' ],
			[ 'subscriber' ],
		];
	}

	public function test_existing_password_stops_working_after_demotion() {
		// REST・XML-RPC のリクエスト中でないと、アプリケーションパスワードでの認証は試みられない
		add_filter( 'application_password_is_api_request', '__return_true' );
		$user             = self::factory()->user->create( [ 'role' => 'administrator' ] );
		list( $password ) = WP_Application_Passwords::create_new_application_password( $user, [ 'name' => 'test' ] );
		$login            = get_userdata( $user )->user_login;
		$this->assertInstanceOf( WP_User::class, wp_authenticate_application_password( null, $login, $password ), '管理者なら認証できる' );

		( new WP_User( $user ) )->set_role( 'editor' );
		$result = wp_authenticate_application_password( null, $login, $password );
		$this->assertWPError( $result, '編集者に降格したら使えない' );
		$this->assertSame( 'application_passwords_disabled_for_user', $result->get_error_code() );
	}
}
