<?php
/**
 * 安否情報の投稿タイプ設定
 *
 * @package hametuha
 */

/**
 * 安否情報の投稿タイプとタクソノミーを登録
 */
add_action( 'init', function () {
	// 安否情報
	$args = array(
		'label'                 => '安否情報',
		'labels'                => [
			'name'               => '安否情報',
			'singular_name'      => '安否情報',
			'add_new'            => '安否報告する',
			'add_new_item'       => '安否報告する',
			'edit_item'          => '安否情報を編集',
			'new_item'           => '安否報告をする',
			'view_item'          => '安否情報を表示',
			'search_items'       => '安否情報を検索',
			'not_found'          => '安否情報が見つかりませんでした',
			'not_found_in_trash' => 'ゴミ箱に安否情報はありません',
			'all_items'          => 'すべての安否情報',
		],
		'description'           => '破滅派同人の安否を知るための最新情報です。書いていない人のことは心配してあげてください。',
		'public'                => true,
		'menu_position'         => 10,
		'menu_icon'             => 'dashicons-microphone',
		'supports'              => array( 'title', 'editor', 'author', 'comments' ),
		'has_archive'           => true,
		'capability_type'       => [ 'anpi', 'anpis' ],
		'map_meta_cap'          => true,
		'capabilities'          => [
			// 単体
			'edit_post'              => 'edit_anpi',
			'read_post'              => 'read_anpi',
			'delete_post'            => 'delete_anpi',
			'create_posts'           => 'create_anpis',
			// 複数
			'edit_posts'             => 'edit_anpis',
			'publish_posts'          => 'publish_anpis',
			'delete_posts'           => 'delete_anpis',
			'delete_published_posts' => 'delete_published_anpis',
			'delete_private_posts'   => 'delete_private_anpis',
			'edit_published_posts'   => 'edit_published_anpis',
			'edit_private_posts'     => 'edit_private_anpis',
			// 編集権限
			'read_private_posts'     => 'read_private_anpis',
			'edit_others_posts'      => 'edit_others_anpis',
			'delete_others_posts'    => 'delete_others_anpis',
		],
		'show_in_rest'          => true,
		'rest_controller_class' => 'WP_REST_Posts_Controller',
		'rewrite'               => array( 'slug' => 'anpi/archives' ),
	);
	register_post_type( 'anpi', $args );

	// 安否情報カテゴリー
	register_taxonomy( 'anpi_cat', array( 'anpi' ), array(
		'hierarchical'      => true,
		'show_ui'           => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'anpi-cat' ),
		'show_in_rest'      => true,
		'label'             => '安否情報の種類',
		'show_admin_column' => true,
		'capabilities'      => [
			'manage_terms' => 'manage_categories',
			'edit_terms'   => 'manage_categories',
			'delete_terms' => 'manage_categories',
			'assign_terms' => 'edit_anpis',
		],
	) );
} );

/**
 * 安否情報の権限制御
 * read権限を持つユーザーは安否情報を投稿・編集・削除できる
 */
add_filter( 'map_meta_cap', function( $caps, $cap, $user_id, $args ) {
	// そもそも安否情報とは関係ない
	if ( ! preg_match( '/_anpis?$/u', $cap) ) {
		return $caps;
	}
	if ( in_array( $cap, [ 'edit_anpi', 'delete_anpi', 'create_anpi', 'read_anpi' ], true ) ) {
		// 個別の安否情報関連の権限チェック
		if ( ! empty( $args[ 0 ] ) ) {
			$post = get_post( $args[ 0 ] );
			if ( 'read_anpi' === $cap ) {
				if ( $post && 'publish' === $post->post_status ) {
					// 公開されているので誰でも読める、権限必要なし
					return [];
				}
			}
			if ( $post ) {
				if ( (int) $post->post_author === (int) $user_id ) {
					// 自分のはログインが必要
					return [ 'read' ];
				} else {
					return [ 'edit_others_anpis' ];
				}
			}
		}
		// 投稿IDがない場合（新規作成など）はreadがあればOK
		if ( user_can( $user_id, 'read' ) ) {
			return [ 'read' ];
		}
		return [ 'do_not_allow' ];
	} elseif ( in_array( $cap, [ 'edit_others_anpis', 'delete_others_anpis', 'read_private_anpis' ], true ) ) {
		// 他人の編集権限
		return [ 'edit_others_posts' ];
	}
	// それ以外（edit_anpis, publish_anpis, delete_anpisなど）はreadが必要
	if ( user_can( $user_id, 'read' ) ) {
		return [ 'read' ];
	}
	return $caps;
}, 10, 4 );

/**
 * 安否情報で読み込むテンプレートを変更する
 */
add_filter( 'template_include', function( $template ) {
	if ( ! is_tax( 'anpi_cat' ) ) {
		return $template;
	}
	return get_template_directory() . '/archive-anpi.php';
} );
