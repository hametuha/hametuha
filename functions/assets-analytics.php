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
	try {
		if ( ! class_exists( 'Gianism\\Service\\Google' ) ) {
			throw new Exception( 'Gianismがインストールされていません。', 500 );
		}
		$google  = Gianism\Service\Google::get_instance();
		$ga      = $google->ga;
		$view_id = $google->ga_profile['view'];
		if ( ! $ga || ! $view_id ) {
			throw new \Exception( 'Google Analytics is not connected.', 500 );
		}
		$params = wp_parse_args( $params, [
			'max-results' => 10,
			'dimensions'  => 'ga:pageTitle',
			'sort'        => '-ga:pageviews',
		] );
		$result = $ga->data_ga->get( 'ga:' . $view_id, $start, $end, $metrics, $params );
		if ( $result && count( $result->rows ) > 0 ) {
			return $result->rows;
		} else {
			return new WP_Error( 404, '該当する結果はありませんでした。' );
		}
	} catch ( Exception $e ) {
		return new WP_Error( $e->getCode(), $e->getMessage() );
	}
}

/**
 * Google Analyticsのトラッキングコードを登録する
 *
 * @action wp_head
 * @ignore
 */
function _hametuha_ga_code() {
	?>
	<script>
		// Adsense
		window.google_analytics_uacct = "UA-1766751-2";
		// analytics.js
		(function (i, s, o, g, r, a, m) {
			i['GoogleAnalyticsObject'] = r;
			i[r] = i[r] || function () {
					(i[r].q = i[r].q || []).push(arguments)
				}, i[r].l = 1 * new Date();
			a = s.createElement(o),
				m = s.getElementsByTagName(o)[0];
			a.async = 1;
			a.src = g;
			m.parentNode.insertBefore(a, m)
		})(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga');

		ga('create', 'UA-1766751-2', 'auto');
		ga('require', 'displayfeatures');
		ga('require', 'linkid', 'linkid.js');
		<?php if ( is_user_logged_in() ) : ?>
		ga('set', '&uid', <?= get_current_user_id() ?>);
		<?php endif; ?>
		<?php if ( is_singular() && ! is_preview() ) : ?>
		ga('set', 'dimension1', '<?= get_post_type() ?>');
		ga('set', 'dimension2', '<?= get_post()->post_author ?>');
		<?php
		$cat = false;
		foreach ( get_the_category( get_the_ID() ) as $c ) {
			$cat = $c->term_id;
		}
		if ( $cat ) :
		?>
		ga('set', 'dimension3', '<?= $cat ?>');
		<?php endif; ?>
		<?php endif; ?>
		<?php if ( is_404() ) : ?>
		ga('set', 'dimension4', '404');
		<?php elseif ( is_admin() ) : ?>
		ga('set', 'dimension4', 'admin');
		<?php elseif ( is_ranking() ) : ?>
		ga('set', 'dimension4', 'ranking');
		<?php endif; ?>
		ga('send', 'pageview');
	</script>
	<?php
}

add_action( 'wp_head', '_hametuha_ga_code', 19 );
add_action( 'admin_head', '_hametuha_ga_code', 19 );


/**
 * Google Analytics用のCookieを設定する
 *
 * @param string $page
 */
function ga_record_cookie( $page ) {
	if ( session_id() || session_start() ) {
		$_SESSION['wpga_page'] = $page;
	}
}


/**
 * Facebookのピクセルタグを書き込む
 */
add_action( 'wp_head', function () {
	echo <<<HTML
<!-- Facebook Pixel Code -->
<script>
!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
document,'script','//connect.facebook.net/en_US/fbevents.js');
fbq('init', '956989844374988');
fbq('track', "PageView");</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id=956989844374988&ev=PageView&noscript=1"
/></noscript>
<!-- End Facebook Pixel Code -->
HTML;
}, 100 );


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

