<?php get_header(); ?>

<?php get_header( 'breadcrumb' ); ?>


<div class="container archive">

	<?php get_template_part( 'templates/news/nav', 'news' ); ?>

	<div class="row">

		<div class="col-xs-12 col-md-9 main-container">

			<div class="archive-meta">
				<h1>
					<?php get_template_part( 'parts/h1' ); ?>
					<span class="label label-default"><?php echo number_format_i18n( loop_count() ); ?>件</span>
				</h1>

				<?php get_template_part( 'parts/meta', 'term' ); ?>

				<?php if ( $desc = term_description() ) : ?>
					<div class="description-wrapper">
						<?php echo wpautop( $desc ); ?>
					</div>

				<?php endif; ?>
			</div>

			<div class="row news-ad__archive">
				<p class="news-ad__title">Ads by Google</p>
				<?php google_adsense( 4 ); ?>
			</div>

			<?php if ( is_tax() && ( $content = get_term_meta( get_queried_object_id(), '_term_content', true ) ) ) : ?>
				<div class="post-content clearfix">
					<?php echo apply_filters( 'the_content', $content ); ?>
				</div>
			<?php endif; ?>

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
				<?php google_adsense( 4 ); ?>
			</div>

			<?php wp_pagenavi(); ?>

			<?php get_search_form(); ?>

			<?php get_template_part( 'templates/news/block', 'keywords' ); ?>

			<?php get_template_part( 'parts/jumbotron', 'news' ); ?>

		</div>
		<!-- //.main-container -->

		<?php get_sidebar( 'news' ); ?>

	</div><!-- // .row -->

</div><!-- //.container -->

<?php get_footer(); ?>
