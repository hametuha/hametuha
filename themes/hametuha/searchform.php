<?php
/**
 * 検索フォーム
 *
 * 特定の投稿タイプに絞り込むこと
 */
if ( is_hamenew() ) {
	$action = get_post_type_archive_link( 'news' );
	$label     = 'はめにゅー内を検索します';
} elseif ( is_post_type_archive( 'faq' ) || is_tax( 'faq_cat' ) || is_singular( 'faq' ) ) {
	$action = get_post_type_archive_link( 'faq' );
	$label     = 'よくある質問を検索します';
} elseif ( is_post_type_archive( 'thread' ) || is_tax( 'topic' ) || is_singular( 'thread' ) ) {
	$action = get_post_type_archive_link( 'thread' );
	$label     = '掲示板の中を検索します';
} elseif ( is_post_type_archive( 'series' ) || is_singular( 'series' ) ) {
	$action = get_post_type_archive_link( 'series' );
	$label     = '連載・作品集を検索します';
} else {
	$action = home_url( 'search' );
	$post_type = 'post';
	$label     = '検索ワードを入れてください';
}
?>
<form method="get" action="<?php echo esc_url( $action ); ?>" class="adv-search-form">
	<div class="input-group mt-5 mb-5">
		<input placeholder="<?php echo esc_attr( $label ); ?>" type="search" name="s" class="form-control"
			   value="<?php the_search_query(); ?>">
		<input type="submit" class="btn btn-primary" value="検索">
	</div><!-- /input-group -->
</form>
