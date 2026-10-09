<?php

namespace Hametuha\Hooks;

use WPametu\Pattern\Singleton;

/**
 * 管理者に2要素認証を必須にする
 *
 * Two Factor プラグイン（wp.org `two-factor`）を前提とする。
 *
 * - 管理者が使えるのは TOTP とバックアップコードだけ。メールは管理者のメールが
 *   乗っ取られたらパスワードリセットと同時に破られるので、2要素目にならない
 * - 管理者以外には2要素認証を提供しない（hashboard のせいでプロフィール画面に入れないため）
 * - 未設定の管理者には管理画面で通知する（権限は止めない）
 * - Gianism のソーシャルログインは wp_login を発火しないので2要素認証を通らない。
 *   連携先の乗っ取り対策を信頼し、当面は許可する（hametuha/gianism#179 で制御予定）
 *
 * @feature-group two-factor
 * @see https://github.com/hametuha/hametuha/issues/430
 */
class TwoFactor extends Singleton {

	/**
	 * 2要素認証を必須にするロール
	 */
	const ROLE = 'administrator';

	/**
	 * 管理者が使えるプロバイダー
	 */
	const PROVIDERS = [ 'Two_Factor_Totp', 'Two_Factor_Backup_Codes' ];

	/**
	 * {@inheritDoc}
	 */
	protected function __construct( array $setting = array() ) {
		if ( ! class_exists( 'Two_Factor_Core' ) ) {
			return;
		}
		add_filter( 'two_factor_providers_for_user', [ $this, 'filter_providers' ], 10, 2 );
		add_filter( 'two_factor_is_required_for_user', [ $this, 'filter_required' ], 10, 2 );
		add_action( 'admin_notices', [ $this, 'admin_notice' ] );
	}

	/**
	 * 2要素認証の対象ユーザーか
	 *
	 * @param \WP_User|int|null $user User.
	 * @return bool
	 */
	public static function is_target( $user ) {
		if ( ! $user instanceof \WP_User ) {
			$user = get_userdata( (int) $user );
		}
		return $user && in_array( self::ROLE, (array) $user->roles, true );
	}

	/**
	 * ユーザーが使えるプロバイダーを絞る
	 *
	 * @param \Two_Factor_Provider[] $providers Providers indexed by key.
	 * @param \WP_User|int|null      $user      User.
	 * @return \Two_Factor_Provider[]
	 */
	public function filter_providers( $providers, $user ) {
		if ( ! self::is_target( $user ) ) {
			return [];
		}
		return array_intersect_key( $providers, array_flip( self::PROVIDERS ) );
	}

	/**
	 * 管理者以外は2要素認証を求めない
	 *
	 * 管理者から降格したユーザーに設定が残っていると、プロバイダーが全部消えたとみなされて
	 * ログインできなくなる（Two Factor は fail closed）。それを防ぐ。
	 *
	 * @param bool     $required Required.
	 * @param \WP_User $user     User.
	 * @return bool
	 */
	public function filter_required( $required, $user ) {
		return self::is_target( $user ) ? $required : false;
	}

	/**
	 * 2要素認証が未設定の管理者に知らせる
	 *
	 * @return void
	 */
	public function admin_notice() {
		$user = wp_get_current_user();
		if ( ! self::is_target( $user ) || \Two_Factor_Core::is_user_using_two_factor( $user->ID ) ) {
			return;
		}
		printf(
			'<div class="notice notice-error"><p>管理者は2要素認証が必須です。<a href="%s">認証アプリ（TOTP）とバックアップコードを設定してください</a>。</p></div>',
			esc_url( admin_url( 'profile.php#two-factor-options' ) )
		);
	}
}
