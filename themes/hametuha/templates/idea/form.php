<?php
/**
 * アイデア投稿フォームのスクリプト
 *
 * @feature-group idea
 */
if ( current_user_can( 'read' ) ) {
	wp_enqueue_script( 'hametuha-components-ideas' );
	// ジャンル
	$terms      = get_terms( [
		'taxonomy'   => 'post_tag',
		'hide_empty' => false,
		'parent'     => 0,
		'meta_query' => [
			[
				'key'   => 'genre',
				'value' => 'サブジャンル',
			],
		],
	] );
	$terms_json = ( $terms && ! is_wp_error( $terms ) ) ? array_map( function ( $term ) {
		return [
			'id'   => $term->term_id,
			'name' => $term->name,
		];
	}, $terms ) : [];
	$js         = <<<JS
window.HametuhaIdeaTags = JSON.parse( '%s' );
JS;
	wp_add_inline_script( 'hametuha-components-ideas', sprintf( $js, json_encode( $terms_json ) ), 'before' );
}
