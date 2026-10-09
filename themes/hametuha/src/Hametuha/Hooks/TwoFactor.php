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
 * - 管理者権限は「2要素認証を通ったセッション」にだけ与える。それ以外のセッションでは
 *   read だけに落とす。Gianism や hameslack は wp_login を発火せずに
 *   wp_set_auth_cookie() を直接呼ぶので、ログイン経路を1つずつ塞ぐより確実
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
		add_filter( 'user_has_cap', [ $this, 'restrict_caps' ], 10, 4 );
		// Gianism のログインは権限が落ちるだけで意味がないので、ログイン自体を断る
		add_action( 'gianism_before_set_login_cookie', [ $this, 'reject_social_login' ] );
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
	 * 権限の制限を有効にするか
	 *
	 * ユニットテストは wp_set_current_user() だけでログインしセッションを持たないので、
	 * テスト環境ではこれで切る。
	 *
	 * @return bool
	 */
	public static function is_enforced() {
		return (bool) apply_filters( 'hametuha_two_factor_enforced', true );
	}

	/**
	 * 現在のセッションが2要素認証を通っているか
	 *
	 * @return bool
	 */
	public static function is_verified_session() {
		return (bool) \Two_Factor_Core::is_current_user_session_two_factor();
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
	 * 2要素認証を通っていない管理者のセッションでは read だけにする
	 *
	 * ロール名は残す。hashboard は has_cap( 'administrator' ) でプロフィール画面への
	 * アクセスを判定しており、消すと2要素認証を設定する画面に入れなくなる。
	 *
	 * @param bool[]   $allcaps All capabilities.
	 * @param string[] $caps    Required primitive capabilities.
	 * @param array    $args    Arguments.
	 * @param \WP_User $user    User.
	 * @return bool[]
	 */
	public function restrict_caps( $allcaps, $caps, $args, $user ) {
		// セッションの話なので、ログイン中の本人の権限だけを扱う
		if ( ! $user->ID || get_current_user_id() !== $user->ID ) {
			return $allcaps;
		}
		// WP-CLI と cron はセッションがない。復旧手段でもあるので止めない
		if ( wp_doing_cron() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			return $allcaps;
		}
		if ( ! self::is_target( $user ) || ! self::is_enforced() || self::is_verified_session() ) {
			return $allcaps;
		}
		return array_intersect_key( $allcaps, array_flip( array_merge( [ 'read', 'level_0' ], (array) $user->roles ) ) );
	}

	/**
	 * 管理者のソーシャルログインを断る
	 *
	 * Gianism はこのアクションで投げた例外をログイン失敗として扱う。
	 *
	 * @param int $user_id User ID.
	 * @throws \Exception 管理者の場合。
	 * @return void
	 */
	public function reject_social_login( $user_id ) {
		if ( self::is_target( $user_id ) ) {
			throw new \Exception( '管理者はソーシャルログインできません。ユーザー名とパスワードでログインしてください。' );
		}
	}

	/**
	 * 権限が落ちていることを知らせる
	 *
	 * @return void
	 */
	public function admin_notice() {
		$user = wp_get_current_user();
		if ( ! self::is_target( $user ) || self::is_verified_session() ) {
			return;
		}
		if ( \Two_Factor_Core::is_user_using_two_factor( $user->ID ) ) {
			$message = sprintf(
				'2要素認証を通らずにログインしているため、管理者権限が無効になっています。<a href="%s">ログインし直してください</a>。',
				esc_url( wp_logout_url( wp_login_url() ) )
			);
		} else {
			$message = sprintf(
				'管理者は2要素認証が必須です。<a href="%s">認証アプリ（TOTP）とバックアップコードを設定する</a>まで、管理者権限は無効になっています。',
				esc_url( admin_url( 'profile.php#two-factor-options' ) )
			);
		}
		printf( '<div class="notice notice-error"><p>%s</p></div>', wp_kses_post( $message ) );
	}
}
