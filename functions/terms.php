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
 * @param string 		   $class_name タグのクラス名
 * @return string[]
 */
function hametuha_terms_to_hashtag( $taxonomies, $post = null, $link = false, $prefix = '#', $class_name = 'tag-link' ) {
	$assigned_terms = [];
	$taxonomies = (array) $taxonomies;
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
