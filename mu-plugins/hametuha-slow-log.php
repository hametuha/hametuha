<?php
/**
 * Plugin Name: Hametuha Slow Request Logger
 *
 * Description: 閾値超のリクエストをテキスト＆EMFで記録。緊急のURI特定と、ルート別の長期トレンド可視化を両立。
 * Version: 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/** 設定 */
if ( ! defined( 'SLOWREQ_THRESHOLD_SEC' ) ) {
	define( 'SLOWREQ_THRESHOLD_SEC', (float) 3.0 ); // これ以上かかったら「遅い」と認定
}
if ( ! defined( 'SLOWREQ_EMIT_EMF' ) ) {
	define( 'SLOWREQ_EMIT_EMF', true );
}
if ( ! defined( 'SLOWREQ_TEXT_TAG' ) ) {
	define( 'SLOWREQ_TEXT_TAG', '[WP SlowRequest]' );                              // テキスト行の識別子
}

$GLOBALS[ '__slowreq_start_ts' ] = microtime( true );

/**
 * アクセスタイプ分類
 *
 * @return string
 */
function slowreq_access_type(): string {
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		return 'cli';
	}
	if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
		return 'cron';
	}
	if ( wp_doing_ajax() ) {
		return 'ajax';
	}
	if ( wp_is_serving_rest_request() ) {
		return 'rest';
	}
	if ( function_exists( 'is_admin' ) && is_admin() ) {
		return 'admin';
	}

	return 'front';
}

/** ルートの“バケット名”を決める（長期統計用） */
function slowreq_route_bucket(): string {
	// RESTはURIから素直にまとめる
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		$uri = $_SERVER[ 'REQUEST_URI' ] ?? '';
		// クエリを除いたパス部分だけ。末尾のIDなどは落として大枠で合わせる
		$path = parse_url( $uri, PHP_URL_PATH ) ?: '';
		// 例: /wp-json/wp/v2/posts/123 -> /wp-json/wp/v2/posts
		$path = preg_replace( '~/\d+(/|$)~', '', $path );

		return 'rest:' . $path;
	}

	// WordPressの条件分岐（front/admin用）
	if ( function_exists( 'is_front_page' ) && is_front_page() ) {
		return 'front-page';
	}
	if ( function_exists( 'is_home' ) && is_home() ) {
		return 'blog-home';
	}
	if ( function_exists( 'is_search' ) && is_search() ) {
		return 'search';
	}
	if ( function_exists( 'is_404' ) && is_404() ) {
		return '404';
	}

	// ニュース系（例: カスタム投稿タイプ news）
	if ( function_exists( 'is_post_type_archive' ) && is_post_type_archive( 'news' ) ) {
		return 'news-archive';
	}
	if ( function_exists( 'is_singular' ) && is_singular( 'news' ) ) {
		return 'news-single';
	}

	// ふつうの投稿／固定ページ
	if ( function_exists( 'is_singular' ) && is_singular( 'post' ) ) {
		return 'single-post';
	}
	if ( function_exists( 'is_singular' ) && is_singular( 'page' ) ) {
		return 'page';
	}

	if ( function_exists( 'is_category' ) && is_category() ) {
		return 'category';
	}
	if ( function_exists( 'is_tag' ) && is_tag() ) {
		return 'tag';
	}
	if ( function_exists( 'is_tax' ) && is_tax() ) {
		$qo = get_queried_object();

		return $qo && ! empty( $qo->taxonomy ) ? 'tax:' . $qo->taxonomy : 'tax';
	}
	if ( function_exists( 'is_archive' ) && is_archive() ) {
		return 'archive';
	}

	return 'other';
}

/**
 * 出力
 *
 * ファイルがあれば置き換える
 *
 * @todo: ターゲットファイルが破滅派専用なので、変えられるようにする
 */
function slowreq_emit_line( string $line ): void {
	$default_file = '/var/log/php-fpm/www-slow.log';
	if ( file_exists( $default_file ) ) {
		$id     = 3;
		$target = $default_file;
		$line  .= PHP_EOL;
	} else {
		$id     = 0;
		$target = null;
	}
	error_log( $line, $id, $target );
}

/**
 * フッターにGA4用のコードを出す
 */
add_action( 'wp_footer', function() {
	if ( is_admin() ) {
		return;
	}
	// かかった時間をマイクロ秒（少数）からミリ秒（整数）に変換
	$total     = ( microtime( true ) - $GLOBALS[ '__slowreq_start_ts' ] ) * 1000;
	$server_ms = (int) round( $total ); // サーバー処理時間(概算)
	// 任意: ルート分類などを付けたい場合
	$route = slowreq_route_bucket();
	?>
	<script>
		(function() {
			window.dataLayer = window.dataLayer || [];
			function gtag() {
				dataLayer.push( arguments );
			}
			gtag( 'event', 'wp_server_time', {
				wp_server_ms: <?php echo (int) $server_ms; ?>,
				route: '<?php echo esc_js( $route ); ?>'
			} );
		})();
	</script>
	<?php
}, PHP_INT_MAX );

/**
 * シャットダウン関数で記録する
 */
add_action( 'shutdown', function () {
	$elapsed = microtime( true ) - ( $GLOBALS[ '__slowreq_start_ts' ] ?? microtime( true ) );
	if ( $elapsed < SLOWREQ_THRESHOLD_SEC ) {
		return;
	}

	$elapsed_ms = (int) round( $elapsed * 1000 );
	$method     = $_SERVER[ 'REQUEST_METHOD' ] ?? '';
	$uri        = $_SERVER[ 'REQUEST_URI' ] ?? '';
	$host       = $_SERVER[ 'HTTP_HOST' ] ?? ( function_exists( 'home_url' ) ? parse_url( home_url(), PHP_URL_HOST ) : '' );
	$status     = function_exists( 'http_response_code' ) ? (int) http_response_code() : 0;
	$user_id    = function_exists( 'get_current_user_id' ) ? (int) get_current_user_id() : 0;
	$blog_id    = function_exists( 'get_current_blog_id' ) ? (int) get_current_blog_id() : 0;
	$access     = slowreq_access_type();
	$route      = slowreq_route_bucket();
	$req_id     = $_SERVER[ 'REQUEST_ID' ] ?? ( $_SERVER[ 'HTTP_X_REQUEST_ID' ] ?? '' );
	$now_iso    = ( new DateTime( 'now', function_exists( 'wp_timezone' ) ? wp_timezone() : new DateTimeZone( date_default_timezone_get() ) ) )->format( DateTime::ATOM );

	// ① 緊急用：人が読みやすい一行
	slowreq_emit_line( sprintf(
		'%s %.2fs %s %s type=%s route=%s status=%d user=%d req_id=%s',
		SLOWREQ_TEXT_TAG, $elapsed, $method, $uri, $access, $route, $status, $user_id, $req_id
	) );

	// ② 長期統計用：EMF（埋め込みメトリクス）でメトリク化
	if ( SLOWREQ_EMIT_EMF ) {
		$emf = [
			"_aws"       => [
				"Timestamp"         => (int) round( microtime( true ) * 1000 ),
				"CloudWatchMetrics" => [
					[
						"Namespace"  => "App/SlowRequest",
						// 重要：ディメンションは増やしすぎない（コスト＆管理）
						"Dimensions" => [ [ "Site", "AccessType", "Route" ] ],
						"Metrics"    => [ [ "Name" => "ElapsedMs", "Unit" => "Milliseconds" ] ],
					]
				],
			],
			"Site"       => (string) $host,
			"AccessType" => (string) $access,
			"Route"      => (string) $route,   // ← “ニュースvs投稿”の比較はここで効く
			"ElapsedMs"  => $elapsed_ms,

			// 解析補助（ディメンションではない）
			"Time"       => $now_iso,
			"Method"     => $method,
			"URI"        => $uri,
			"Status"     => $status,
			"ReqId"      => $req_id,
			"BlogId"     => $blog_id,
			"UserId"     => $user_id,
		];
		slowreq_emit_line( json_encode( $emf, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) );
	}
} );
