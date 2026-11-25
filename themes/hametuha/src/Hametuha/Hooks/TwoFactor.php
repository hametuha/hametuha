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
		// Two Factor の保存（優先度10）のあとで、強制するプロバイダーを保存値から外す
		add_action( 'personal_options_update', [ $this, 'strip_forced_from_meta' ], 11 );
		add_action( 'edit_user_profile_update', [ $this, 'strip_forced_from_meta' ], 11 );
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
		$role = self::get_role( $user );
		if ( ! $role ) {
			return false;
		}
		// 強制するロールは、サイト全体の設定でメールが無効になっていても通さない（fail closed）。
		// プロバイダーが無いのでログインは失敗するが、パスワードだけで通すよりよい。管理者には admin_notice で知らせる
		if ( ! empty( self::FORCED[ $role ] ) ) {
			return true;
		}
		return $required;
	}

	/**
	 * 強制するプロバイダーをユーザーの保存値に残さない
	 *
	 * 強制分は filter_enabled() で毎回足すので、保存する必要がない。
	 * 残っていると、編集者をユーザー編集画面で管理者に昇格させたとき、管理者には使えないメールだけが
	 * 保存値に残り、Two Factor が「プロバイダーが消えた」とみなしてログインを拒否する。
	 *
	 * @param int $user_id User ID.
	 * @return void
	 */
	public function strip_forced_from_meta( $user_id ) {
		$role = self::get_role( $user_id );
		if ( ! $role || empty( self::FORCED[ $role ] ) ) {
			return;
		}
		$stored = get_user_meta( $user_id, \Two_Factor_Core::ENABLED_PROVIDERS_USER_META_KEY, true );
		if ( ! is_array( $stored ) ) {
			return;
		}
		$rest = array_values( array_diff( $stored, self::FORCED[ $role ] ) );
		if ( $rest !== $stored ) {
			update_user_meta( $user_id, \Two_Factor_Core::ENABLED_PROVIDERS_USER_META_KEY, $rest );
		}
	}

	/**
	 * 強制するプロバイダーのうち、サイト全体の設定で無効になっているもの
	 *
	 * @return string[]
	 */
	public static function disabled_forced_providers() {
		$registered = array_keys( \Two_Factor_Core::get_providers() );
		$forced     = array_unique( array_merge( ...array_values( self::FORCED ) ) );
		return array_values( array_diff( $forced, $registered ) );
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
		if ( 'administrator' !== self::get_role( $user ) ) {
			return;
		}
		$disabled = self::disabled_forced_providers();
		if ( $disabled ) {
			printf(
				'<div class="notice notice-error"><p>2要素認証の設定で %s が無効になっているため、編集者がログインできません。<a href="%s">有効にしてください</a>。</p></div>',
				esc_html( implode( ', ', $disabled ) ),
				esc_url( admin_url( 'options-general.php?page=two-factor-settings' ) )
			);
		}
		if ( \Two_Factor_Core::is_user_using_two_factor( $user->ID ) ) {
			return;
		}
		printf(
			'<div class="notice notice-error"><p>管理者は2要素認証が必須です。<a href="%s">認証アプリ（TOTP）とバックアップコードを設定してください</a>。</p></div>',
			esc_url( admin_url( 'profile.php#two-factor-options' ) )
		);
	}
}
