<?php get_template_part( 'templates/faq/header-faq' ); ?>

	<div class="container single">

		<div class="row">
			<?php
			if ( have_posts() ) :
				while ( have_posts() ) :
					the_post();
					?>

				<article itemscope
						 itemtype="http://schema.org/BlogPosting" <?php post_class( 'col-xs-12 col-sm-9 main-container' ); ?>>

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

					<?php get_template_part( 'parts/alert', 'old' ); ?>

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

					<?php get_template_part( 'parts/share' ); ?>

					<?php get_template_part( 'parts/author', 'narrow' ); ?>

					<div class="more">
						<?php comments_template(); ?>
					</div>

					<?php google_adsense( 'related' ); ?>

				</article><!-- //.single-container -->

					<?php
			endwhile;
endif;
			?>

			<?php get_template_part( 'templates/faq/sidebar-faq' ); ?>

		</div><!-- //.row -->

	</div><!-- //.container -->

<?php
get_footer();
