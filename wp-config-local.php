<?php
/**
 * ローカル開発用の追加設定
 *
 * このファイルはGitで管理せず、開発者ごとに異なる設定を記述できます。
 * プラグイン固有のデバッグ定数などをここに追加してください。
 */


// プラグイン・テーマ・コアの自動更新を無効化
define( 'AUTOMATIC_UPDATER_DISABLED', true );
define( 'WP_AUTO_UPDATE_CORE', false );

// ファイル編集を無効化
define( 'DISALLOW_FILE_EDIT', true );
define( 'DISALLOW_FILE_MODS', true );

// 環境変数の設定
define( 'WP_ENVIRONMENT_TYPE', 'local' );

// Query Monitor プラグイン用
define( 'QM_ENABLE_CAPS_PANEL', true );

// プラグイン開発用
define( 'SAVEQUERIES', true );

// メール送信のデバッグ（Mailpit使用）
define( 'WPMS_ON', true );
define( 'WPMS_SMTP_HOST', 'mailpit' );
define( 'WPMS_SMTP_PORT', 1025 );

// その他のカスタム定数
// define( 'CUSTOM_CONSTANT', 'value' );

// 特定のプラグインのデバッグモード
 define( 'JETPACK_DEV_DEBUG', true );
