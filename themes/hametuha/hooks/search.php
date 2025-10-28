<?php
/**
 * 検索関連のフック
 *
 */


/**
 * 検索クエリでpost_typeが指定されていない場合、postに限定する
 *
 * @param WP_Query $query
 */
add_action( 'pre_get_posts', function ( $query ) {
	// 管理画面、REST API、またはメインクエリでない場合はスキップ
	if ( is_admin() || ! $query->is_main_query() || wp_is_rest_endpoint() ) {
		return;
	}

	// 検索クエリでpost_typeが指定されていない場合
	if ( $query->is_search() && ! $query->get( 'post_type' ) ) {
		$query->set( 'post_type', 'post' );
	}
} );

/**
 * 検索用のクエリばーを追加する
 *
 * @param array $vars
 * @return array
 */
add_filter( 'query_vars', function( $vars ) {
	$vars[] = 't'; // タグ（複数指定）
	$vars[] = 'length'; // 長さ
	return $vars;
} );

/**
 * 複数タグでのOR検索を実装
 *
 * クエリパラメータ:
 * - t: カンマ区切りのタグ (例: t=SF,恋愛,ミステリー)
 * - tags[]: 配列形式のタグ (例: tags[]=SF&tags[]=恋愛)
 * - tag: フリー入力のタグ (例: tag=SF)
 *
 * @param WP_Query $query
 */
add_action( 'pre_get_posts', function ( WP_Query $query ) {
	$tags = hametuha_queried_tags( $query );
	if ( ! empty( $tags ) ) {
		// 既存のtax_queryを取得
		$tax_query = $query->get( 'tax_query' ) ?: [];

		// タグのOR検索を追加
		$tax_query[] = [
			'taxonomy' => 'post_tag',
			'field'    => 'name',
			'terms'    => $tags,
			'operator' => 'IN', // OR検索
		];

		$query->set( 'tax_query', $tax_query );
	}
} );

/**
 * 投稿保存時に文字数を計算・保存
 *
 * @param int     $post_id
 * @param WP_Post $post
 */
add_action( 'save_post', function( $post_id, $post ) {
	// リビジョン、自動保存は除外
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}

	// 対象の投稿タイプのみ
	if ( ! in_array( $post->post_type, [ 'post', 'series' ], true ) ) {
		return;
	}

	// 公開済みまたは非公開の投稿のみ
	if ( ! in_array( $post->post_status, [ 'publish', 'private' ], true ) ) {
		return;
	}

	// 文字数を計算して保存
	$length = get_post_length( $post );
	update_post_meta( $post_id, '_post_length', $length );
	// もし連載に含まれる投稿だったら、親（連載）の文字数を更新
	if ( $post->post_parent && 'series' === get_post_type( $post->post_parent ) ) {
		$length = get_post_length( $post->post_parent );
		update_post_meta( $post->post_parent, '_post_length', $length );
	}
}, 20, 2 );

/**
 * 投稿の長さが指定されている場合は絞り込み
 */
add_action( 'pre_get_posts', function( WP_Query $query ) {
	$length = $query->get( 'length' );
	if ( empty( $length ) ) {
		// 長さは指定されていない
		return;
	}

	// 長さの範囲を取得
	$ranges = hametuha_length_ranges( (array) $length );
	if ( empty( $ranges ) ) {
		return;
	}

	// 既存のmeta_queryを取得
	$meta_query = $query->get( 'meta_query' ) ?: [];

	// 複数範囲をOR条件で追加
	$length_query = [ 'relation' => 'OR' ];
	foreach ( $ranges as $range ) {
		$length_query[] = [
			'key'     => '_post_length',
			'value'   => [ $range['min'], $range['max'] ],
			'type'    => 'NUMERIC',
			'compare' => 'BETWEEN',
		];
	}

	$meta_query[] = $length_query;
	$query->set( 'meta_query', $meta_query );
} );
