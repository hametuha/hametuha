<?php get_header() ?>

<?php get_header( 'breadcrumb' ) ?>

	<div class="container archive">

		<div class="row">

			<div class="col-xs-12 col-md-9 main-container">

				<div class="archive-meta">
					<h1>
						<?php get_template_part( 'parts/h1' ); ?>
						<span class="label label-default"><?php echo number_format_i18n( loop_count() ); ?>件</span>
					</h1>

					<div class="desc">
						<?php get_template_part( 'parts/meta-desc' ); ?>
					</div>

				</div>
				
				<div class="row news-ad__archive">
					<p class="news-ad__title">Ads by Google</p>
					<?php google_adsense( 4 ) ?>
				</div>


				<!-- Tab panes -->
				<ol class="archive-container media-list">
					<?php
					while ( have_posts() ) {
						the_post();
						get_template_part( 'parts/loop', get_post_type() );
					}
					?>
				</ol>

				<div class="row news-ad__archive">
					<p class="news-ad__title">Ads by Google</p>
					<?php google_adsense( 4 ) ?>
				</div>

				<?php wp_pagenavi(); ?>

				<?php get_search_form(); ?>

				<?php if ( $terms = hamenew_popular_nouns() ) : ?>
				<hr/>
				<h2 class="news-keywords__title">人気のキーワード</h2>
				<p class="news-keywords__wrapper">
					<?= implode( ' ', array_map( function ( $term ) {
						return sprintf( '<a href="%s" class="news-keywords__link"><i class="icon-tag6"></i> %s</a>', get_term_link( $term ), esc_html( $term->name ) );
					}, $terms ) ); ?>
				</p>
				<?php endif; ?>

				<?php get_template_part( 'parts/jumbotron', 'news' ) ?>

			</div>
			<!-- //.main-container -->

			<?php get_sidebar( 'news' ) ?>

		</div><!-- // .row -->

	</div><!-- //.container -->

<?php get_footer(); ?>
