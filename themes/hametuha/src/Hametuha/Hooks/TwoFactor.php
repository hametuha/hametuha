<?php

namespace Hametuha\Hooks;

use WPametu\Pattern\Singleton;

/**
 * 管理者と編集者に2要素認証を必須にする
 *
 * Two Factor プラグイン（wp.org `two-factor`）を前提とする。
 *
 * - 管理者が使えるのは TOTP とバックアップコードだけ。メールは管理者のメールが
 *   乗っ取られたらパスワードリセットと同時に破られるので、2要素目にならない。
 *   未設定の管理者には管理画面で通知する（権限は止めない）
 * - 編集者はメールの確認コードを強制する。設定は不要で、本人は外せない。
 *   使い回したパスワードの漏洩には効くが、メールの乗っ取りには効かない
 * - それ以外のロールには2要素認証を提供しない（hashboard のせいでプロフィール画面に入れないため）
 * - Gianism のソーシャルログインは wp_login を発火しないので2要素認証を通らない。
 *   連携先の乗っ取り対策を信頼し、当面は許可する（hametuha/gianism#179 で制御予定）
 *
 * @feature-group two-factor
 * @see https://github.com/hametuha/hametuha/issues/430
 */
class TwoFactor extends Singleton {

	/**
	 * ロールごとに使えるプロバイダー。上にあるロールほど優先する
	 */
	const POLICIES = [
		'administrator' => [ 'Two_Factor_Totp', 'Two_Factor_Backup_Codes' ],
		'editor'        => [ 'Two_Factor_Email' ],
	];

	/**
	 * 本人の設定にかかわらず有効にするプロバイダー
	 */
	const FORCED = [
		'editor' => [ 'Two_Factor_Email' ],
	];

	/**
	 * {@inheritDoc}
	 */
	protected function __construct( array $setting = array() ) {
		if ( ! class_exists( 'Two_Factor_Core' ) ) {
			return;
		}
		add_filter( 'two_factor_providers_for_user', [ $this, 'filter_providers' ], 10, 2 );
		add_filter( 'two_factor_enabled_providers_for_user', [ $this, 'filter_enabled' ], 10, 2 );
		add_filter( 'two_factor_is_required_for_user', [ $this, 'filter_required' ], 10, 2 );
		add_action( 'admin_notices', [ $this, 'admin_notice' ] );
	}

	/**
	 * 2要素認証の方針を決めるロール
	 *
	 * @param \WP_User|int|null $user User.
	 * @return string|null 対象外なら null
	 */
	public static function get_role( $user ) {
		if ( ! $user instanceof \WP_User ) {
			$user = get_userdata( (int) $user );
		}
		if ( ! $user ) {
			return null;
		}
		foreach ( array_keys( self::POLICIES ) as $role ) {
			if ( in_array( $role, (array) $user->roles, true ) ) {
				return $role;
			}
		}
		return null;
	}

	/**
	 * ユーザーが使えるプロバイダーを絞る
	 *
	 * @param \Two_Factor_Provider[] $providers Providers indexed by key.
	 * @param \WP_User|int|null      $user      User.
	 * @return \Two_Factor_Provider[]
	 */
	public function filter_providers( $providers, $user ) {
		$role = self::get_role( $user );
		if ( ! $role ) {
			return [];
		}
		return array_intersect_key( $providers, array_flip( self::POLICIES[ $role ] ) );
	}

	/**
	 * 強制するプロバイダーを有効にする
	 *
	 * @param string[] $enabled Enabled provider keys.
	 * @param int      $user_id User ID.
	 * @return string[]
	 */
	public function filter_enabled( $enabled, $user_id ) {
		$role = self::get_role( $user_id );
		if ( ! $role || empty( self::FORCED[ $role ] ) ) {
			return $enabled;
		}
		return array_values( array_unique( array_merge( (array) $enabled, self::FORCED[ $role ] ) ) );
	}

	/**
	 * 対象外のロールには2要素認証を求めない
	 *
	 * 管理者・編集者から降格したユーザーに設定が残っていると、プロバイダーが全部消えたとみなされて
	 * ログインできなくなる（Two Factor は fail closed）。それを防ぐ。
	 *
	 * @param bool     $required Required.
	 * @param \WP_User $user     User.
	 * @return bool
	 */
	public function filter_required( $required, $user ) {
		return self::get_role( $user ) ? $required : false;
	}

	/**
	 * 2要素認証が未設定の管理者に知らせる
	 *
	 * 編集者は強制で有効になるので、知らせる必要がない。
	 *
	 * @return void
	 */
	public function admin_notice() {
		$user = wp_get_current_user();
		if ( 'administrator' !== self::get_role( $user ) || \Two_Factor_Core::is_user_using_two_factor( $user->ID ) ) {
			return;
		}
		printf(
			'<div class="notice notice-error"><p>管理者は2要素認証が必須です。<a href="%s">認証アプリ（TOTP）とバックアップコードを設定してください</a>。</p></div>',
			esc_url( admin_url( 'profile.php#two-factor-options' ) )
		);
	}
}
