<?php
/**
 * スレッドを開始するボタン
 *
 * @feature-group thread
 */

if ( ! function_exists( 'hamethread_button' ) ) {
	trigger_error( '関数hamethread_buttonが存在しません。', E_USER_WARNING ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error -- hamethread プラグインが無いことを知らせる
	return;
}
hamethread_button();
