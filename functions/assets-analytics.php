<?php

/**
 * Googleのランキングを取得する
 *
 * @param string $start
 * @param string $end
 * @param array $params
 * @param string $metrics
 *
 * @return WP_Error|array
 */
function hametuha_ga_ranking( $start, $end, $params = [], $metrics = 'ga:pageviews' ) {
	return \Hametuha\Hooks\Analytics::get_instance()->ranking( $start, $end, $params, $metrics );
}


/**
 * セッションに書き込みがあったらGAに記録する
 */
add_action( 'admin_init', function () {
	if ( ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) && ( session_id() || session_start() ) && isset( $_SESSION['wpga_page'] ) ) {
		$page = esc_js( $_SESSION['wpga_page'] );
		unset( $_SESSION['wpga_page'] );
		add_action( 'admin_notices', function () use ( $page ) {
			echo <<<HTML
<script>
try{
    ga('send', 'pageview', {
        page: '{$page}',
        title: '投稿完了'
    });
}catch(err){}
</script>
HTML;
		} );
	}
} );

/**
 * 投稿公開時に可能であればセッションに記録を付与
 *
 */
add_action( 'transition_post_status', function ( $new, $old, WP_Post $post ) {
	switch ( $new ) {
		case 'publish':
		case 'future':
			if ( ! defined( 'DOING_CRON' ) || ! DOING_CRON ) {
				ga_record_cookie( sprintf( '/wp-admin/edit-done/%d', $post->ID ) );
			}
			break;
		default:
			// Do nothing
			break;
	}
}, 10, 3 );

