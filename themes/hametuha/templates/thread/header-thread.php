<?php
/**
 * スレッドのヘッダー
 *
 * @feature-group thread
 */
get_header();
?>

<div class="sub-header thread-header">
	<div class="container">

		<div class="row sub-header-box">
			<div class="col col-xs-12 col-sm-8">
				<h1 class="sub-header-title">破滅派掲示板</h1>
			</div>
			<div class="col col-xs-12 col-sm-4">
				<form action="<?php echo get_post_type_archive_link( 'thread' ); ?>">
					<div class="input-group">
						<input type="search" class="form-control hamelp-search-input" name="s" placeholder="掲示板を検索" value="<?php the_search_query(); ?>" />
						<span class="input-group-btn">
							<button class="btn btn-secondary hamelp-search-button" type="submit">検索</button>
						  </span>
					</div><!-- /input-group -->
				</form>
			</div>
		</div>


		<div class="row">
			<div class="col col-xs-12 col-sm-9">
				<ul class="nav nav-pills">
					<li class="<?php echo is_post_type_archive( 'thread' ) ? 'active' : ''; ?>">
						<a href="<?php echo get_post_type_archive_link( 'thread' ); ?>">ホーム</a>
					</li>
					<?php
					$terms = get_terms( 'topic' );
					if ( $terms && ! is_wp_error( $terms ) ) :
						foreach ( $terms as $term ) :
							?>
						<li class="<?php echo is_tax( $term->taxonomy, $term->name ) || ( is_singular( 'faq' ) && has_term( $term->term_id, $term->taxonomy ) ) ? 'active' : ''; ?>">
							<a href="<?php echo get_term_link( $term ); ?>"><?php echo esc_html( $term->name ); ?></a>
						</li>
							<?php
					endforeach;
endif;
					?>
				</ul>
			</div>
			<div class="col hidden-xs col-sm-3 text-right">
				<a class="btn btn-link" href="<?php echo home_url(); ?>"><i class="icon-exit"></i> 破滅派へ戻る</a>
			</div>
		</div>

	</div>
</div>

<?php if ( ! is_page() ) {
	get_header( 'breadcrumb' );
} ?>
