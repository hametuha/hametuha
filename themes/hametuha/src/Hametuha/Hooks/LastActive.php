<?php

namespace Hametuha\Hooks;

use WPametu\Pattern\Singleton;

/**
 * ユーザーの最終利用日を記録する
 *
 * ログイン日時ではなく「最後にサイトを使った日」を記録する。
 * 「ログイン状態を保持」のユーザーは毎日来ても再ログインしないため。
 * ページキャッシュ下でも cookie-tasting のリフレッシュで PHP に到達するので、
 * init で拾えば足りる。書き込みは1日1回まで。
 *
 * @see https://github.com/hametuha/hametuha/issues/425
 */
class LastActive extends Singleton {

	const META_KEY = 'last_active';

	/**
	 * {@inheritDoc}
	 */
	protected function __construct( array $setting = array() ) {
		add_action( 'init', [ $this, 'record_current_user' ] );
		add_action( 'wp_login', [ $this, 'record_login' ], 10, 2 );
		// 管理画面のユーザー一覧
		add_filter( 'manage_users_columns', [ $this, 'add_column' ] );
		add_filter( 'manage_users_custom_column', [ $this, 'render_column' ], 10, 3 );
		add_filter( 'manage_users_sortable_columns', [ $this, 'sortable_columns' ] );
		add_action( 'pre_user_query', [ $this, 'sort_users' ] );
	}

	/**
	 * ログイン中のユーザーの利用を記録する
	 *
	 * @return void
	 */
	public function record_current_user() {
		if ( wp_doing_cron() || ! is_user_logged_in() ) {
			return;
		}
		$this->record( get_current_user_id() );
	}

	/**
	 * ログイン時に記録する
	 *
	 * @param string   $user_login Login name.
	 * @param \WP_User $user       User object.
	 * @return void
	 */
	public function record_login( $user_login, $user ) {
		$this->record( $user->ID );
	}

	/**
	 * 最終利用日を記録する
	 *
	 * @param int      $user_id User ID.
	 * @param int|null $now     Timestamp. Defaults to now.
	 * @return bool 書き込んだら true
	 */
	public function record( $user_id, $now = null ) {
		$now  = $now ?? time();
		$last = self::get( $user_id );
		if ( $last && wp_date( 'Y-m-d', $last ) === wp_date( 'Y-m-d', $now ) ) {
			// 今日は記録済み
			return false;
		}
		return (bool) update_user_meta( $user_id, self::META_KEY, $now );
	}

	/**
	 * 最終利用日を取得する
	 *
	 * @param int $user_id User ID.
	 * @return int タイムスタンプ。記録がなければ 0
	 */
	public static function get( $user_id ) {
		return (int) get_user_meta( $user_id, self::META_KEY, true );
	}

	/**
	 * ユーザー一覧に列を追加する
	 *
	 * @param array $columns Columns.
	 * @return array
	 */
	public function add_column( $columns ) {
		$columns[ self::META_KEY ] = __( '最終利用日', 'hametuha' );
		return $columns;
	}

	/**
	 * 列を描画する
	 *
	 * @param string $output  Output.
	 * @param string $column  Column name.
	 * @param int    $user_id User ID.
	 * @return string
	 */
	public function render_column( $output, $column, $user_id ) {
		if ( self::META_KEY !== $column ) {
			return $output;
		}
		$last = self::get( $user_id );
		if ( ! $last ) {
			return sprintf( '<span class="description">%s</span>', esc_html__( '記録なし', 'hametuha' ) );
		}
		return esc_html( wp_date( get_option( 'date_format' ), $last ) );
	}

	/**
	 * ソート可能にする
	 *
	 * @param array $columns Sortable columns.
	 * @return array
	 */
	public function sortable_columns( $columns ) {
		$columns[ self::META_KEY ] = self::META_KEY;
		return $columns;
	}

	/**
	 * 最終利用日で並べ替える
	 *
	 * meta_query の OR + NOT EXISTS はキー指定なしの JOIN を作り、記録のないユーザーが
	 * 別のメタの値で並んでしまうため、JOIN と ORDER BY を直接書く。
	 * 記録のないユーザーは NULL になり、降順で末尾に来る。
	 *
	 * @param \WP_User_Query $query User query.
	 * @return void
	 */
	public function sort_users( $query ) {
		global $wpdb;
		if ( ! is_admin() || self::META_KEY !== ( $query->query_vars['orderby'] ?? '' ) ) {
			return;
		}
		$order                = 'ASC' === strtoupper( $query->query_vars['order'] ?? '' ) ? 'ASC' : 'DESC';
		$query->query_from   .= $wpdb->prepare(
			" LEFT JOIN {$wpdb->usermeta} AS last_active ON ( {$wpdb->users}.ID = last_active.user_id AND last_active.meta_key = %s )",
			self::META_KEY
		);
		$query->query_orderby = "ORDER BY CAST( last_active.meta_value AS UNSIGNED ) {$order}, {$wpdb->users}.ID {$order}";
	}
}
