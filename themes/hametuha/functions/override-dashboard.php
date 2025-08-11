<?php
/**
 * 管理画面のフッターに表示するテキスト
 *
 * @param string $string
 *
 * @return string
 */
add_filter( 'admin_footer_text', function ( $string ) {
	return '<span id="footer-thankyou">破滅派は<a href="https://ja.wordpress.org/">WordPress</a>で動いています。</span>';
} );

/**
 * Allow editor components to have styles.
 */
add_action( 'admin_enqueue_scripts', function() {
	wp_enqueue_style( 'wp-components' );
} );

/**
 * ダッシュボードのメタボックスをカスタマイズ
 */
function _hametuha_admin_dashboard_metaboxes( $screen_id ) {
	global $wp_meta_boxes;
	if ( $screen_id == 'dashboard' ) {
		$meta_boxes = array(
			'normal' => array(
				'network_dashboard_right_now', // 現在の状況（ネットワーク管理）
				'dashboard_plugins', // プラグイン
			),
			'side'   => array(
				'dashboard_quick_press', // クイック投稿
				'dashboard_primary', // WordPress 開発ブログ
				'dashboard_secondary', // WordPress フォーラム
			),
		);
		foreach ( $meta_boxes as $context => $arr ) {
			foreach ( $arr as $id ) {
				if ( isset( $wp_meta_boxes[ $screen_id ][ $context ]['core'][ $id ] ) ) {
					remove_meta_box( $id, $screen_id, $context );
				}
			}
		}
	}
}

add_action( 'do_meta_boxes', '_hametuha_admin_dashboard_metaboxes' );

/**
 * メタボックスを削除する
 *
 * @param string $post_type
 * @param string $context
 * @param object $post
 */
function _hametuha_remove_metabox( $post_type, $context ) {
	switch ( $context ) {
		case 'normal':
			if ( 'post' == $post_type ) {
				//カスタムフィールド
				remove_meta_box( 'postcustom', $post_type, $context );
				//トラックバック
				remove_meta_box( 'trackbacksdiv', $post_type, $context );
				//スラッグDIV
				remove_meta_box( 'slugdiv', $post_type, $context );
			}
			break;
		case 'side':
			//Simple Tagsの設定
			remove_meta_box( 'simpletags-settings', $post_type, $context );
			break;
	}
}

add_action( 'do_meta_boxes', '_hametuha_remove_metabox', 100000, 3 );


/**
 * 投稿画面におけるユーザーの表示項目を上書きする
 *
 * @param array $result
 * @param string $option
 * @param WP_User $user
 */
add_filter( 'get_user_option_metaboxhidden_post', function ( $result, $option, $user = null ) {
	$result                  = (array) $result;
	$box_to_hide_from_author = array( 'authordiv' );
	foreach ( $box_to_hide_from_author as $option ) {
		if ( ! current_user_can( 'edit_others_posts' ) && false === array_search( $option, $result ) ) {
			$result[] = $option;
		}
	}

	return $result;
}, 1, 3 );


/**
 * 検閲用情報を入力する
 */
add_action( 'admin_init', function() {
	// 検閲
	add_settings_section( 'censorship', '検閲', function () {
		echo '<p>コンテンツの検閲を行うための設定です。正規表現が使えます。デリミタは<code>#</code>です。</p>';
	}, 'discussion' );
	// 検閲ブラックリスト
	add_settings_field( 'four_words', '検閲対象文字列', function ( $args ) {
		printf( '<textarea rows="10" style="width: 90%%;" name="four_words" id="four_words">%s</textarea>', esc_textarea( get_option( 'four_words', '' ) ) );
	}, 'discussion', 'censorship' );
	register_setting( 'discussion', 'four_words' );
} );

