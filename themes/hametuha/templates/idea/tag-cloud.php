<?php
/**
 * アイデア用のタグクラウドを出力する
 *
 * @feature-group idea
 */
// すでにキャッシュがあれば、それを使う
$cache = get_transient( 'templates/ideas/tag-cloud' );
if ( false !== $cache ) {
	echo $cache;
	return;
}

// ページを作りながらキャッシュを生成する
$terms = get_terms( [
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
if ( ! $terms || is_wp_error( $terms ) ) {
	return '';
}
ob_start();
?>
<h2 class="dividing-header"><?php esc_html_e( 'アイデアで良く使われるタグ', 'hametuha' ); ?></h2>
<div class="tag-cloud mb-5">
	<?php
	echo wp_generate_tag_cloud( array_map( function ( $term ) {
		$term->link = add_query_arg( [
			'tag' => rawurlencode( $term->slug ),
		], get_post_type_archive_link( 'ideas' ) );
		return $term;
	}, $terms ) );
	?>
</div>
<?php
$content = ob_get_contents();
ob_end_flush();
set_transient( 'templates/ideas/tag-cloud', $content, 60 * 60 );
