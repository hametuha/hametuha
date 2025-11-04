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
	$vars[] = 'comments'; // コメント数
	$vars[] = 'rating'; // レーティング（星評価の平均）
	$vars[] = 'reaction'; // レビュータグ（複数指定可能）
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

/**
 * コメント数が指定されている場合は絞り込み
 */
add_filter( 'posts_where', function( $where, WP_Query $query ) {
	$comments = $query->get( 'comments' );
	if ( empty( $comments ) || ! is_numeric( $comments ) ) {
		return $where;
	}

	global $wpdb;
	$min_comments = absint( $comments );

	// comment_countはwp_postsテーブルに直接あるので、シンプルに絞り込める
	$where .= $wpdb->prepare( " AND {$wpdb->posts}.comment_count >= %d", $min_comments );

	return $where;
}, 10, 2 );

/**
 * レーティング（星評価の平均）が指定されている場合は絞り込み
 *
 * クエリパラメータ:
 * - rating=1: 1点台（1.0～1.9）
 * - rating=2: 2点台（2.0～2.9）
 * - rating=3: 3点台（3.0～3.9）
 * - rating=4: 4点台（4.0～5.0）
 */
add_action( 'pre_get_posts', function( WP_Query $query ) {
	$rating = $query->get( 'rating' );
	if ( empty( $rating ) || ! is_numeric( $rating ) ) {
		return;
	}

	$min_rating = absint( $rating );
	// 1～4のみ許可
	if ( ! in_array( $min_rating, range( 1, 4 ), true ) ) {
		return;
	}

	// 既存のmeta_queryを取得
	$meta_query = $query->get( 'meta_query' ) ?: [];

	// 各点数の範囲を定義
	$ranges = [
		1 => [ 1.0, 1.9 ],
		2 => [ 2.0, 2.9 ],
		3 => [ 3.0, 3.9 ],
		4 => [ 4.0, 5.0 ],
	];

	// レーティング平均値での絞り込みを追加
	$meta_query[] = [
		'key'     => '_rating_average',
		'value'   => $ranges[ $min_rating ],
		'type'    => 'NUMERIC',
		'compare' => 'BETWEEN',
	];

	$query->set( 'meta_query', $meta_query );
} );

/**
 * レビュータグが指定されている場合は絞り込み
 *
 * クエリパラメータ:
 * - reaction[]=知的&reaction[]=泣ける: 複数のレビュータグで絞り込み（AND条件）
 *
 * 各タグは _review_tag_{タグ名} というpost_metaに件数が保存されている
 * 1件以上獲得しているものを絞り込む
 */
add_action( 'pre_get_posts', function( WP_Query $query ) {
	$reviews = $query->get( 'reaction' );
	if ( empty( $reviews ) ) {
		return;
	}

	// 配列に変換
	if ( ! is_array( $reviews ) ) {
		$reviews = [ $reviews ];
	}

	// 有効なレビュータグのリストを取得
	$review_model = \Hametuha\Model\Review::get_instance();
	$valid_tags   = [];
	foreach ( $review_model->feedback_tags as $key => $terms ) {
		$valid_tags = array_merge( $valid_tags, $terms );
	}

	// 指定されたタグをフィルタリング
	$reviews = array_filter( $reviews, function( $tag ) use ( $valid_tags ) {
		return in_array( $tag, $valid_tags, true );
	} );

	if ( empty( $reviews ) ) {
		return;
	}

	// 既存のmeta_queryを取得
	$meta_query = $query->get( 'meta_query' ) ?: [];

	// 各レビュータグでAND条件を追加
	foreach ( $reviews as $tag ) {
		$meta_query[] = [
			'key'     => '_review_tag_' . $tag,
			'value'   => 1,
			'type'    => 'NUMERIC',
			'compare' => '>=',
		];
	}

	$query->set( 'meta_query', $meta_query );
} );
