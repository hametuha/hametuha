<?php
/*
 * Template Name: はめにゅー
 */
get_header();
?>

<?php get_header( 'breadcrumb' ); ?>

	<div class="container single">

		<div class="row">

			<?php
			if ( have_posts() ) :
				while ( have_posts() ) :
					the_post();
					?>

				<article <?php post_class( 'col-xs-12 col-md-9 main-container' ); ?>>

					<!-- title -->
					<div class="page-header">

						<h1 class="post-title news__title">
							<?php the_title(); ?>
						</h1>

					</div><!-- //.page-header -->

					<!-- Meta data -->
					<div <?php post_class( 'post-meta' ); ?>>

						<?php get_template_part( 'parts/meta', 'single' ); ?>

					</div><!-- //.post-meta -->

					<?php get_template_part( 'parts/meta', 'thumbnail' ); ?>

					<?php if ( has_excerpt() ) : ?>
						<div class="news-excerpt">
							<?php the_excerpt(); ?>
						</div>
					<?php endif; ?>

					<div class="post-content clearfix" itemprop="articleBody">
						<?php the_content(); ?>
					</div><!-- //.post-content -->

					<?php
					wp_link_pages(
						[
							'before'      => '<div class="row"><p class="link-pages clrB">ページ: ',
							'after'       => '</p></div>',
							'link_before' => '<span>',
							'link_after'  => '</span>',
						]
					);
					?>


					<div class="row">
											<?php google_adsense( 2 ); ?>
							<p class="news-ad__title">Ads by Google</p>
					</div>

					<hr/>

					<?php get_template_part( 'parts/jumbotron', 'news' ); ?>

				</article><!-- //.single-container -->

							<?php
			endwhile;
endif;
			?>

			<?php get_sidebar( 'news' ); ?>

		</div><!-- //.row -->


	</div><!-- //.container -->

<?php
get_footer();
