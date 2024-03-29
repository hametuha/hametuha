<?php
/**
 * エディタースタイルを適用する
 *
 * 投稿以外と投稿でCSSを分ける
 */
add_action( 'admin_head', function () {
	$screen = get_current_screen();
	if ( ! $screen ) {
		return;
	}
	switch ( $screen->post_type ) {
		case 'post':
		case 'series':
			add_editor_style( 'assets/css/editor-style-post.css' );
			break;
		default:
			add_editor_style( 'assets/css/editor-style.css' );
			break;
	}
} );


/**
 * TinyMCEの初期化配列を作成する
 *
 * @param array $initArray
 *
 * @return array
 */
add_filter( 'tiny_mce_before_init', function ( $initArray, $editor_id ) {

	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	$css_dir = get_stylesheet_directory() . '/assets/css';
	if ( ! isset( $initArray['cache_suffix'] ) ) {
		$initArray['cache_suffix'] = '?foo=var';
	}
	$initArray['cache_suffix'] .= sprintf( '&hametuha-%s', date_i18n( 'YmdHis', max( filemtime( $css_dir . '/editor-style.css' ), filemtime( $css_dir . '/editor-style-post.css' ) ) ) );

	//選択できるブロック要素を変更
	$initArray['block_formats'] = 'Paragraph=p;大見出し=h2;中見出し=h3;小見出し=h4;最小見出し=h5;整形済みテキスト=pre';
	//使用できるタグを指定
	if ( empty( $initArray['extended_valid_elements'] ) ) {
		$initArray['extended_valid_elements'] = 'iframe[id|class|title|style|align|frameborder|height|longdesc|marginheight|marginwidth|name|scrolling|src|width]';
	} else {
		$elements                             = explode( ',', $initArray['extended_valid_elements'] );
		$elements[]                           = 'iframe[id|class|title|style|align|frameborder|height|longdesc|marginheight|marginwidth|name|scrolling|src|width]';
		$initArray['extended_valid_elements'] = implode( ',', $elements );
	}
	// 独自スタイル
	$styles = [];
	// 装飾
	$styles[] = [
		'title' => '文字装飾',
		'items' => [
			[
				'title'   => '圏点',
				'inline'  => 'span',
				'classes' => 'text-emphasis',
			],
			[
				'title'   => '太字+圏点',
				'inline'  => 'strong',
				'classes' => 'text-emphasis',
			],
			[
				'title'  => '編注',
				'inline' => 'small',
			],
			[
				'title'   => '脚注',
				'inline'  => 'small',
				'classes' => 'footernote-ref',
			],
		],
	];
	// ブロック
	$styles[] = [
		'title' => 'ブロック',
		'items' => [
			[
				'title'   => 'コラム',
				'block'   => 'aside',
				'wrapper' => true,
			],
			[
				'title'   => 'ドロップキャップ',
				'block'   => 'p',
				'classes' => 'has-drop-cap',
			],
			[
				'title'   => 'ROW',
				'block'   => 'div',
				'classes' => 'row',
				'wrapper' => true,
			],
		],
	];
	if ( $screen ) {
		switch ( $screen->post_type ) {
			case 'news':
				$styles[] = [
					'title' => 'インタビュー',
					'items' => [
						[
							'title'   => '回答者',
							'inline'  => 'span',
							'classes' => 'interviewee',
						],
						[
							'title'   => '聞き手',
							'block'   => 'p',
							'classes' => 'interviewer',
						],
					],
				];
				break;
			case 'newsletter':
				$styles[] = [
					'title' => 'ボタン',
					'items' => [
						[
							'title'    => 'リンクボタン',
							'selector' => 'a',
							'classes'  => 'btn',
						],
					],
				];
				break;
		}
	}
	$initArray['style_formats'] = json_encode( $styles );

	if ( ! is_admin() ) {
		$initArray['menubar'] = false;
	}

	return $initArray;
}, 10001, 2 );


/**
 * タイトルを出す
 *
 * @param WP_Post $post
 */
add_action( 'edit_form_after_title', function ( WP_Post $post ) {
	if ( 'post' == $post->post_type ) {
		echo '<h3>本文</h3>';
	}
}, 10000 );

/**
 * プラグイン用JSを登録する
 *
 * @param array $plugin_array
 *
 * @return array
 */
add_filter( 'mce_external_plugins', function ( array $plugin_array ) {
	$plugin_array['hametuha'] = get_stylesheet_directory_uri() . '/assets/js/dist/admin/mce.js';

	return $plugin_array;
} );
