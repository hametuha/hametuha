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
	return array_filter( $terms, function( $term ) {
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
	return array_map( function( WP_Term $term ) use ( $link, $prefix, $class_name ) {
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
	$args = [
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
	usort( $tags, function( $a, $b ) {
		return $b->count - $a->count;
	} );
	// 上限数で切り取り
	return array_slice( $tags, 0, $limit );
}
