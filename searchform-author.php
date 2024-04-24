<?php
/**
 * 執筆者検索フォーム
 */
?>

<form method="get" action="<?php echo home_url( '/authors/search/' ); ?>" class="adv-search-form">

	<div class="input-group">
		<input placeholder="<?php esc_attr_e( '検索したい名前を入れて下さい', 'hametuha' ); ?>" type="text" name="s" class="form-control"
			   value="<?php the_search_query(); ?>">
		<span class="input-group-btn">
			<input type="submit" class="btn btn-default" value="検索">
		</span>
	</div><!-- /input-group -->

	<p class="search-author-link text-center">
		<a class="btn btn-lg btn-primary" href="<?php echo home_url( '/authors/search/' ); ?>"><?php esc_html_e( 'すべての執筆者', 'hametuha' ); ?></a>
		<a class="btn btn-lg btn-primary" href="<?php echo home_url( '/authors/' ); ?>"><?php esc_html_e( '執筆者トップ', 'hametuha' ); ?></a>
	</p>
</form>
