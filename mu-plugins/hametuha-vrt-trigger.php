<?php
/**
 * Plugin Name: Hametuha VRT Trigger
 * Description: WordPress自動アップデート完了時にGitHub Actions経由でPercy VRTをトリガーする
 * Version: 1.0.0
 * Author: Hametuha
 */

// phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_error_log

defined( 'ABSPATH' ) || exit;

// 本番環境でのみ動作
if ( 'production' !== wp_get_environment_type() ) {
	return;
}

/**
 * VRT トリガー用の cron フック名。
 */
const HAMETUHA_VRT_CRON_HOOK = 'hametuha_trigger_vrt';

/**
 * 更新情報を蓄積する option キー。
 */
const HAMETUHA_VRT_PENDING_OPTION = 'hametuha_vrt_pending_updates';

/**
 * WordPress アップデート完了時に VRT cron を予約する。
 *
 * 5分後に Percy VRT をトリガーする cron を登録。
 * 既に予約済みの場合はキャンセルしてリスケジュールすることで、
 * 連続アップデート時にまとめて1回だけ VRT を実行する。
 *
 * @param WP_Upgrader $upgrader   Upgrader インスタンス。
 * @param array       $hook_extra アップデート情報。
 */
add_action( 'upgrader_process_complete', function ( $upgrader, $hook_extra ) {
	if ( ! defined( 'GITHUB_PAT' ) || empty( GITHUB_PAT ) ) {
		return;
	}

	// update のみ対象（install は除外）
	$action = $hook_extra['action'] ?? '';
	if ( 'update' !== $action ) {
		return;
	}

	// core / plugin のみ対象。
	// theme は GitHub 経由でデプロイするため、サーバー側の自動アップデート対象外。
	$update_type = $hook_extra['type'] ?? '';
	if ( ! in_array( $update_type, [ 'core', 'plugin' ], true ) ) {
		return;
	}

	// 更新種別を蓄積（cron 実行時にまとめて送信）
	$pending = get_option( HAMETUHA_VRT_PENDING_OPTION, [] );
	if ( ! in_array( $update_type, $pending, true ) ) {
		$pending[] = $update_type;
		update_option( HAMETUHA_VRT_PENDING_OPTION, $pending, false );
	}

	// 既存の予約があればキャンセル（リスケジュール）
	$next = wp_next_scheduled( HAMETUHA_VRT_CRON_HOOK );
	if ( $next ) {
		wp_unschedule_event( $next, HAMETUHA_VRT_CRON_HOOK );
	}

	// 5分後に VRT をトリガー
	wp_schedule_single_event( time() + 5 * MINUTE_IN_SECONDS, HAMETUHA_VRT_CRON_HOOK );
	error_log( sprintf( '[VRT Trigger] Scheduled: %s update, VRT in 5 minutes', $update_type ) );
}, 10, 2 );

/**
 * Cron コールバック: GitHub repository_dispatch で Percy VRT をトリガーする。
 */
add_action( HAMETUHA_VRT_CRON_HOOK, function () {
	if ( ! defined( 'GITHUB_PAT' ) || empty( GITHUB_PAT ) ) {
		return;
	}

	// 蓄積された更新種別を取得してクリア
	$update_types = get_option( HAMETUHA_VRT_PENDING_OPTION, [] );
	delete_option( HAMETUHA_VRT_PENDING_OPTION );

	if ( empty( $update_types ) ) {
		return;
	}

	// GitHub repository_dispatch イベントを送信
	$response = wp_remote_post(
		'https://api.github.com/repos/hametuha/hametuha/dispatches',
		[
			'headers' => [
				'Accept'               => 'application/vnd.github+json',
				'Authorization'        => 'Bearer ' . GITHUB_PAT,
				'X-GitHub-Api-Version' => '2022-11-28',
				'Content-Type'         => 'application/json',
			],
			'body'    => wp_json_encode( [
				'event_type'     => 'wordpress-update',
				'client_payload' => [
					'update_types' => $update_types,
					'site_url'     => site_url(),
				],
			] ),
			'timeout' => 15,
		]
	);

	if ( is_wp_error( $response ) ) {
		error_log( sprintf( '[VRT Trigger] Failed: %s', $response->get_error_message() ) );
		return;
	}

	$code = wp_remote_retrieve_response_code( $response );
	if ( 204 === $code ) {
		error_log( sprintf( '[VRT Trigger] Success: %s update(s) triggered VRT', implode( ', ', $update_types ) ) );
	} else {
		error_log( sprintf( '[VRT Trigger] Unexpected response: %d %s', $code, wp_remote_retrieve_body( $response ) ) );
	}
} );
