<?php
/**
 * WordPress の基本設定
 *
 * このファイルはローカルでマウントされるため、
 * Dockerを再ビルドせずに設定を変更できます。
 */

// ** MySQL 設定 - Docker環境用 ** //
define( 'DB_NAME', getenv('WORDPRESS_DB_NAME') ?: 'wordpress' );
define( 'DB_USER', getenv('WORDPRESS_DB_USER') ?: 'wordpress' );
define( 'DB_PASSWORD', getenv('WORDPRESS_DB_PASSWORD') ?: 'wordpress' );
define( 'DB_HOST', getenv('WORDPRESS_DB_HOST') ?: 'mysql:3306' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

/**
 * 破滅派用のテーブル接頭辞
 */
$table_prefix = 'syoko_';

/**
 * 開発者向け: WordPress デバッグモード
 */
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', true );
define( 'SCRIPT_DEBUG', true );

/**
 * サイトURL設定
 */
define( 'WP_HOME', getenv('WP_HOME') ?: 'https://hametuha.info' );
define( 'WP_SITEURL', getenv('WP_SITEURL') ?: 'https://hametuha.info' );

/**
 * その他の設定
 */
define( 'WP_POST_REVISIONS', 10 );
define( 'AUTOSAVE_INTERVAL', 300 );
define( 'WP_MEMORY_LIMIT', '256M' );
define( 'WP_MAX_MEMORY_LIMIT', '512M' );

/**
 * 追加のカスタム設定ファイルを読み込む
 * ここに開発中に必要な定数を追加できます
 */
if ( file_exists( dirname( __FILE__ ) . '/wp-config-local.php' ) ) {
    require_once dirname( __FILE__ ) . '/wp-config-local.php';
}

/**#@+
 * 認証用ユニークキー
 *
 * それぞれを異なるユニーク (一意) な文字列に変更してください。
 * {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org の秘密鍵サービス} で自動生成することもできます。
 */
define( 'AUTH_KEY',          'mp`GF<lhy1P837`<t0#jZFJ[>{2u={b[tC?tnE@|k1I> :Y/;FM*83dIumK{r+**' );
define( 'SECURE_AUTH_KEY',   '78~=Ity(FM?%1[k?B)9{p0,qijRy7m-,81iWb$7@$z R_cX HLBKd9xZj^dh@^Nj' );
define( 'LOGGED_IN_KEY',     '95KV)Pzc>>wic1h#=:t23/5`SF52PV*4ZjbZnaj2#iV5`>3&,L2cV%$xwO5R@>Q8' );
define( 'NONCE_KEY',         'hzPHg_+Ro6cAi_%}:[Y`4BV%IQ=V%*V7smeL?O15|O-3R<If!t|iOtd}_*&]9Gg-' );
define( 'AUTH_SALT',         '>P{[0?Q,V*yf,.uyk/z~e-[|R:2F/xO7+nCng{2rq)8Fk$>2R$!GN6)W_ButEqMr' );
define( 'SECURE_AUTH_SALT',  '*e>!,z <B!VpTwk&}$:3yrOkb=ce+5KDB/h?O0q6PG.?bo.DbcJd[!0+6GP[WKp<' );
define( 'LOGGED_IN_SALT',    ' TW/^R^jB ty>!=h&:NQ2JuY~Ha{J@)*u)_`]R<,a0aZD4JkAVI!KDeSN(cBZH@^' );
define( 'NONCE_SALT',        '=_D!}pK~<?uMTW:.W1K0qu:<!~h<a,yfU5{[|t&j;*Peq=E:v/m`b*q=:5D69nrO' );
define( 'WP_CACHE_KEY_SALT', 'Gyx,D?yl[+@_:XRp =wbXdJ-fmMj]1O|!4ha45/C3fy3ZnhSd<CVb6GFM4YISzj>' );


/**#@-*/

/**
 * WordPress データベーステーブルの接頭辞
 *
 * それぞれにユニーク (一意) な接頭辞を与えることで一つのデータベースに複数の WordPress を
 * インストールすることができます。半角英数字と下線のみを使用してください。
 */
// 上部で定義済み

/* 編集が必要なのはここまでです ! WordPress でのパブリッシングをお楽しみください。 */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );
