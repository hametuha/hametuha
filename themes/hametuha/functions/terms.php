<?php
/**
 * Term related functions.
 */


/**
 * Get nouns for news.
 *
 * @param null $post
 * @param bool $filter
 * @return array
 */
function hametuha_get_nouns( $post = null, $filter = true ) {
	$terms = get_the_terms( get_post( $post ), 'nouns' );
	if ( is_wp_error( $terms ) || ! $terms ) {
		return [];
	}
	if ( ! $filter ) {
		return $terms;
	}
	return array_filter( $terms, function ( $term ) {
		return 1 < $term->count;
	} );
}

/**
 * タクソノミーのタグリストを取得
 *
 * @param string[]|string  $taxonomies タクソノミーの配列
 * @param null|int|WP_Post $post       投稿オブジェクトかID
 * @param bool             $link       リンクを付けるかどうか
 * @param string           $prefix     ハッシュタグの場合は#、それ以外は@
 * @param string           $class_name タグのクラス名
 * @return string[]
 */
function hametuha_terms_to_hashtag( $taxonomies, $post = null, $link = false, $prefix = '#', $class_name = 'tag-link' ) {
	$assigned_terms = [];
	$taxonomies     = (array) $taxonomies;
	foreach ( $taxonomies as $taxonomy ) {
		$terms = get_the_terms( get_post( $post ), $taxonomy );
		if ( is_wp_error( $terms ) || ! $terms ) {
			continue;
		}
		foreach ( $terms as $term ) {
			$assigned_terms[] = $term;
		}
	}
	return array_map( function ( WP_Term $term ) use ( $link, $prefix, $class_name ) {
		$label = $prefix . $term->name;
		return $link ? sprintf( '<a class="%s" href="%s">%s</a>', esc_attr( $class_name ), esc_url( get_term_link( $term ) ), esc_html( $label ) ) : $label;
	}, $assigned_terms );
}

/**
 * 破滅派における特別なタグ。
 *
 * メタキーは`genre`
 *
 * @return string[]
 */
function hametuha_tag_types() {
	return [
		'サブジャンル',
		'固有名詞',
		'フラグ',
		'印象',
		'一般名詞',
	];
}

/**
 * 破滅派において特別なタグ
 *
 * @param string|string[] $types 指定した場合はそのタイプだけ。
 * @return WP_Term[]
 */
function hametuha_get_canonical_tags( $types = [] ) {
	// hametuha_tag_typesでラベルを取得
	// nameが指定されている場合はそのタイプ
	$types = (array) $types;
	if ( empty( $types ) ) {
		$types = hametuha_tag_types();
	}
	// meta_query で genre in $typesで検索する
	$args  = [
		'taxonomy'   => 'post_tag',
		'hide_empty' => false,
		'meta_query' => [
			[
				'key'     => 'genre',
				'value'   => $types,
				'compare' => 'IN',
			],
		],
	];
	$terms = get_terms( $args );
	if ( is_wp_error( $terms ) ) {
		return [];
	}
	return $terms;
}

/**
 * 破滅派の特別なタグのうち、人気のものを返す
 *
 * @param int             $limit 上限数
 * @param string|string[] $types 指定した場合はそのタイプだけ。
 * @return WP_Term[]
 */
function hametuha_get_popular_tags( $limit = 5, $types = [] ) {
	// 特別なタグを取得
	$tags = hametuha_get_canonical_tags( $types );
	if ( empty( $tags ) ) {
		return [];
	}
	// $term->countで降順ソート
	usort( $tags, function ( $a, $b ) {
		return $b->count - $a->count;
	} );
	// 上限数で切り取り
	return array_slice( $tags, 0, $limit );
}

/**
 * 現在していされているタグを返す
 *
 * @param WP_Query|null $query 指定しない場合はメインクエリ
 * @return string[]
 */
function hametuha_queried_tags( $query = null ) {
	if ( is_null( $query ) ) {
		global $wp_query;
		$query = $wp_query;
	}
	$terms = $query->get( 't' );
	if ( empty( $terms ) ) {
		return [];
	}
	// 強制的に配列に
	$terms = (array) $terms;
	// カンマ区切り形式を許容
	$tags = [];
	foreach ( $terms as $term ) {
		foreach ( explode( ',', $term ) as $t ) {
			$t = trim( $t );
			if ( ! empty( $t ) ) {
				$tags[] = $t;
			}
		}
	}
	// 重複を除去して返す
	return array_values( array_unique( $tags ) );
}

/**
 * Get tags from recent posts with minimum total count
 *
 * @param int $recent_days 最近の投稿の日数（デフォルト30日）
 * @param int $min_count タグの最小総件数（デフォルト10件）
 * @param int $limit 表示するタグの最大数（デフォルト20件）
 * @return WP_Term[] タグの配列
 */
function hametuha_get_popular_recent_tags( $recent_days = 30, $min_count = 10, $limit = 20 ) {
	// トランジェントキーを生成
	$transient_key = sprintf( 'popular_recent_tags_%d_%d_%d', $recent_days, $min_count, $limit );
	$tags          = get_transient( $transient_key );

	if ( false !== $tags ) {
		return $tags;
	}

	// 最近の投稿を取得
	$recent_posts = get_posts( [
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'date_query'     => [
			[
				'after' => sprintf( '%d days ago', $recent_days ),
			],
		],
		'fields'         => 'ids',
	] );

	if ( empty( $recent_posts ) ) {
		return [];
	}

	// それらの投稿についているタグを取得
	$tag_ids = [];
	foreach ( $recent_posts as $post_id ) {
		$post_tags = wp_get_post_tags( $post_id, [ 'fields' => 'ids' ] );
		$tag_ids   = array_merge( $tag_ids, $post_tags );
	}

	// 重複を削除
	$tag_ids = array_unique( $tag_ids );

	if ( empty( $tag_ids ) ) {
		return [];
	}

	// タグオブジェクトを取得（総件数が一定以上のもののみ）
	$tags = get_tags( [
		'include'    => $tag_ids,
		'orderby'    => 'count',
		'order'      => 'DESC',
		'number'     => $limit,
		'hide_empty' => true,
	] );

	// 最小件数でフィルタリング
	$tags = array_filter( $tags, function ( $tag ) use ( $min_count ) {
		return $tag->count >= $min_count;
	} );

	// 3時間キャッシュ
	set_transient( $transient_key, $tags, 3 * HOUR_IN_SECONDS );

	return $tags;
}
