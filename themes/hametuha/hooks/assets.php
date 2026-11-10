<?php

/**
 * Display device width.
 */
function hametuha_viewport() {
	?>
	<meta name="viewport" content="width=device-width,initial-scale=1.0" />
	<?php
}
add_action( 'wp_head', 'hametuha_viewport', 1 );
add_action( 'hashboard_head', 'hametuha_viewport', 1 );

// 画像が引き伸ばしされるフィルターを除去
// see: https://on-ze.com/blog/13082
add_filter( 'wp_img_tag_add_auto_sizes', '__return_false' );

/**
 * アセットを登録する
 *
 * @action init
 */
add_action( 'init', function () {

	// Bootstrap 5 (no jQuery dependency)
	wp_register_script( 'twitter-bootstrap', get_template_directory_uri() . '/assets/vendor/bootstrap.js', [], '5.3.8', true );

	// FontPlus
	wp_register_script( 'font-plus', '//webfont.fontplus.jp/accessor/script/fontplus.js?xnZANi~MEp8%3D&aa=1&chws=1', null, null, false );

	// FontAwesome
	wp_register_style( 'font-awesome', 'https://use.fontawesome.com/releases/v5.6.3/css/all.css', [], '5.6.3' );
	add_filter( 'style_loader_tag', function ( $tag, $handle, $src, $media ) {
		if ( 'font-awesome' !== $handle ) {
			return $tag;
		}
		return str_replace( '/>', "integrity='sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/' crossorigin='anonymous' />", $tag );
	}, 10, 4 );

	// Prop Types (versioned URL to avoid redirects)
	wp_register_script( 'prop-types', 'https://unpkg.com/prop-types@15.8.1/prop-types.min.js', [ 'react' ], '15.8.1', true );

	// Recharts (versioned URL to avoid redirects, depends on react/react-dom)
	wp_register_script( 'recharts', 'https://unpkg.com/recharts@2.15.0/umd/Recharts.js', [ 'prop-types', 'react', 'react-dom' ], '2.15.0', true );

	// Select2
	wp_register_script( 'select2-src', get_template_directory_uri() . '/assets/vendor/select2/select2.min.js', [ 'jquery' ], '4.0.3', true );
	wp_register_script( 'select2', get_template_directory_uri() . '/assets/vendor/select2/i18n/ja.js', [ 'select2-src' ], '4.0.3', true );
	wp_register_style( 'select2', get_template_directory_uri() . '/assets/vendor/select2/select2.min.css', [], '4.0.3' );

	// テーマのアセット（assets/js・assets/css）は wp-dependencies.json からまとめて登録する（#478）。
	// ハンドル名と依存はソース先頭の @handle・@deps に書く。バージョンはファイルの hash。
	$deps = get_template_directory() . '/wp-dependencies.json';
	if ( file_exists( $deps ) ) {
		$assets = json_decode( file_get_contents( $deps ), true );
		if ( ! empty( $assets ) ) {
			foreach ( $assets as $asset ) {
				if ( empty( $asset['path'] ) ) {
					continue;
				}
				switch ( $asset['ext'] ) {
					case 'js':
						$footer = [
							'in_footer' => $asset['footer'],
						];
						if ( isset( $asset['strategy'] ) && in_array( $asset['strategy'], [ 'defer', 'async' ], true ) ) {
							$footer['strategy'] = $asset['strategy'];
						}
						wp_register_script( $asset['handle'], trailingslashit( get_template_directory_uri() ) . $asset['path'], $asset['deps'], $asset['hash'], $footer );
						break;
					case 'css':
						wp_register_style( $asset['handle'], trailingslashit( get_template_directory_uri() ) . $asset['path'], $asset['deps'], $asset['hash'], $asset['media'] ?? 'all' );
						break;
				}
			}
		}
	}

	wp_localize_script( 'hametuha-common', 'HametuhaGlobal', [] );

	// Add custom script.
	wp_localize_script( 'hametuha-components', 'HametuhaComponents', [
		'indicator' => get_template_directory_uri() . '/vendor/hametuha/hashboard/assets/img/ripple.gif',
	] );
}, 9 );

/**
 * スクリプトを読み込む
 *
 */
add_action( 'wp_enqueue_scripts', function () {
	// Common Style.
	if ( ! get_query_var( 'hashboard' ) ) {
		wp_enqueue_style( 'hametuha-app' );
	}

	// Social scripts
	wp_enqueue_script( 'hametuha-social' );

	// Common Scripts (hashboardでは読み込まない - Bootstrap重複回避)
	if ( ! get_query_var( 'hashboard' ) ) {
		wp_enqueue_script( 'hametuha-common' );
	}
	// Single post.
	if ( is_singular( 'post' ) ) {
		wp_enqueue_script( 'hametuha-single' );
	}
	// トップページのお知らせ切り替え（#327）
	if ( is_front_page() ) {
		wp_enqueue_script( 'hametuha-front-announcement' );
	}
	// Series.
	if ( is_singular( 'series' ) ) {
		wp_enqueue_script( 'hametuha-series' );
	}
	// ログイン中ユーザーへの緊急告知（uploads/notification.json）#452
	if ( is_user_logged_in() ) {
		wp_enqueue_script( 'hametuha-emergency-notice' );
		$upload = wp_upload_dir();
		wp_localize_script( 'hametuha-emergency-notice', 'HametuhaEmergencyNotice', [
			// baseurl は siteurl のスキームのままなので、表示中のページに合わせる（混在コンテンツ対策）
			'url'      => set_url_scheme( $upload['baseurl'] . '/notification.json' ),
			'interval' => 120,
		] );
	}
	// Comment.
	if ( is_singular() && ! is_page() ) {
		wp_enqueue_script( 'comment-reply' );
	}
}, 1000 );

/**
 * Dequeue Assets.
 */
add_action( 'wp_enqueue_scripts', function () {
	// If contact form is not
	if ( ! is_singular() || ! has_shortcode( get_queried_object()->post_content, 'contact-form-7' ) ) {
		wp_dequeue_style( 'contact-form-7' );
		wp_dequeue_script( 'contact-form-7' );
		// CF7 本体の reCAPTCHA 連携が有効だと全ページで読み込まれるため外す。
		// 連携を解除すれば読み込まれなくなるが、設定が残っていても漏れないよう残しておく（#458）。
		wp_dequeue_script( 'google-recaptcha' );
		wp_dequeue_script( 'wpcf7-recaptcha' );
		// CF7 本体の Turnstile 連携も全ページで読み込むため外す（#458）。
		// ログイン画面の Turnstile は simple-cloudflare-turnstile の別ハンドル（cfturnstile）なので影響しない。
		wp_dequeue_script( 'cloudflare-turnstile' );
	}
	// wp-pagenavi
	wp_dequeue_style( 'wp-pagenavi' );
	// iframe
	if ( isset( $_GET['iframe'] ) && $_GET['iframe'] ) {
		wp_dequeue_style( 'admin-bar' );
	}
	// Single Page.
	wp_dequeue_style( 'wp-tmkm-amazon' );
}, 1001 );

/**
 * 管理画面でアセットを読み込む
 */
add_action( 'admin_enqueue_scripts', function ( $page = '' ) {

	$screen = get_current_screen();

	wp_enqueue_style( 'hametuha-admin' );

	// プロフィール編集画面
	if ( 'user-edit.php' === $page ) {
		wp_enqueue_media();
		wp_enqueue_script( 'hametuha-user-edit' );
	}
}, 200 );

/**
 * Hashboard用にCSSを読み込む
 *
 */
add_action( 'wp_enqueue_scripts', function () {
	if ( \Hametuha\Hashboard::is_page() ) {
		wp_enqueue_style( 'hametuha-hashboard' );
	}
} );

/**
 * jQueryをフッターに動かす
 */
add_action( 'init', function () {
	if ( is_admin() || 'wp-login.php' === basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
		return;
	}
	global $wp_scripts;
	$jquery     = $wp_scripts->registered['jquery-core'];
	$jquery_ver = $jquery->ver;
	$jquery_src = $jquery->src;
	// Register jQuery again.
	wp_deregister_script( 'jquery' );
	wp_register_script( 'jquery', false, [ 'jquery-core' ], $jquery_ver, true );
	wp_deregister_script( 'jquery-core' );
	wp_register_script( 'jquery-core', $jquery_src, [], $jquery_ver, true );
} );
