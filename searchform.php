<?php
if ( is_hamenew() ) {
	$post_type = 'news';
	$label     = 'はめにゅー内を検索します';
} elseif ( is_post_type_archive( 'faq' ) || is_tax( 'faq_cat' ) || is_singular( 'faq' ) ) {
	$post_type = 'faq';
	$label     = 'よくある質問を検索します';
} elseif ( is_post_type_archive( 'thread' ) || is_tax( 'topic' ) || is_singular( 'thread' ) ) {
	$post_type = 'thread';
	$label     = '掲示板の中を検索します';
} else {
	$post_type = 'any';
	$label     = '検索ワードを入れてください';
}
?>
<form method="get" action="<?= home_url( '/' ) ?>" class="adv-search-form">

	<?php if ( $post_type ) : ?>
		<input type="hidden" name="post_type" value="<?= $post_type ?>"/>
	<?php endif; ?>

	<div class="input-group">
		<input placeholder="<?= esc_attr( $label ) ?>" type="text" name="s" class="form-control"
		       value="<?php the_search_query(); ?>">
        <span class="input-group-btn">
            <input type="submit" class="btn btn-default" value="検索">
        </span>
	</div><!-- /input-group -->
</form>