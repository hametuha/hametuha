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
