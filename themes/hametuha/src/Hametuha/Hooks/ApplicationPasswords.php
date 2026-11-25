<?php

namespace Hametuha\Hooks;

use WPametu\Pattern\Singleton;

/**
 * アプリケーションパスワードを管理者だけに限定する
 *
 * WordPress の既定では全ユーザーが自分のアプリケーションパスワードを作れる。
 * hashboard が管理者以外のプロフィール画面を隠しているが、REST API
 * （POST /wp/v2/users/me/application-passwords）からはログイン中の誰でも発行できてしまう。
 *
 * アプリケーションパスワードは2要素認証を通らない長期の資格情報なので、
 * 編集者に許すと #430 で強制したメールの確認コードの抜け道になる。
 * いまは管理者だけに絞り、端末の認証を通した編集者に許すかは将来検討する。
 *
 * フィルターが false を返すと、発行（画面・REST）だけでなく認証そのものも止まる。
 * 管理者から降格したユーザーの既存のパスワードも、その時点で使えなくなる。
 *
 * @feature-group two-factor
 * @see https://github.com/hametuha/hametuha/issues/533
 */
class ApplicationPasswords extends Singleton {

	/**
	 * アプリケーションパスワードを使える権限
	 */
	const CAPABILITY = 'manage_options';

	/**
	 * {@inheritDoc}
	 */
	protected function __construct( array $setting = array() ) {
		add_filter( 'wp_is_application_passwords_available_for_user', [ $this, 'filter_available' ], 10, 2 );
		add_action( 'load-authorize-application.php', [ $this, 'guard_authorize_screen' ] );
	}

	/**
	 * アプリ連携の承認画面で、保存より先に可否を確かめる
	 *
	 * コアの authorize-application.php は、JavaScript 無効時のフォーム送信（POST）で
	 * パスワードを保存してから可否を確かめる。管理者のときに開いた画面を降格後に送信すると
	 * 発行できてしまうので、画面の読み込み時点で止める。
	 *
	 * @return void
	 */
	public function guard_authorize_screen() {
		if ( wp_is_application_passwords_available_for_user( wp_get_current_user() ) ) {
			return;
		}
		wp_die(
			esc_html__( 'Application passwords are not available for your account. Please contact the site administrator for assistance.', 'default' ),
			esc_html__( 'Cannot Authorize Application', 'default' ),
			[
				'response'  => 403,
				'link_text' => esc_html__( 'Go Back', 'default' ),
				'link_url'  => admin_url(),
			]
		);
	}

	/**
	 * 管理者だけに使わせる
	 *
	 * @param bool     $available 他のフィルターまでの判定。
	 * @param \WP_User $user      User.
	 * @return bool
	 */
	public function filter_available( $available, $user ) {
		return $available && user_can( $user, self::CAPABILITY );
	}
}
