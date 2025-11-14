<?php
/*
 * Template Name: KDP
 *
 * @todo 使ってない？
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

				<article itemscope
						itemtype="http://schema.org/BlogPosting" <?php post_class( 'col-xs-12 col-sm-9 main-container' ); ?>>

					<?php get_template_part( 'parts/meta', 'thumbnail' ); ?>

					<!-- title -->
					<div class="page-header">

						<h1 class="post-title" itemprop="headline">
							<?php the_title(); ?>
						</h1>
					</div><!-- //.page-header -->


					<!-- Meta data -->
					<div <?php post_class( 'post-meta' ); ?>>

						<?php get_template_part( 'parts/meta', 'single' ); ?>

					</div><!-- //.post-meta -->


					<?php if ( has_excerpt() ) : ?>
						<div class="excerpt" itemprop="description">
							<?php the_excerpt(); ?>
						</div><!-- //.excerpt -->
					<?php endif; ?>



					<div class="post-content clearfix" itemprop="articleBody">
						<?php the_content(); ?>
					</div><!-- //.post-content -->



					<?php get_template_part( 'parts/share' ); ?>

				</article><!-- //.single-container -->

					<?php
			endwhile;
endif;
			?>

			<?php get_sidebar(); ?>

		</div><!-- //.row -->

	</div><!-- //.container -->

<?php
get_footer( 'books' );
get_footer();
