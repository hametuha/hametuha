<?php
get_header();
get_header( 'sub' );
get_header( 'breadcrumb' );
?>

	<div class="container single">

		<div class="row">
			<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

				<article itemscope
				         itemtype="http://schema.org/BlogPosting" <?php post_class( 'col-xs-12 col-sm-9 main-container' ) ?>>

					<?php if ( ! is_page() ) : ?>
						<?php get_template_part( 'parts/bar', 'posttype' ) ?>
					<?php endif; ?>

					<?php get_template_part( 'parts/meta', 'thumbnail' ) ?>

					<!-- title -->
					<div class="page-header">

						<h1 class="post-title" itemprop="headline">
							<?php the_title(); ?>
						</h1>
					</div><!-- //.page-header -->


					<!-- Meta data -->
					<div <?php post_class( 'post-meta' ) ?>>

						<?php get_template_part( 'parts/meta', 'single' ) ?>

					</div><!-- //.post-meta -->


					<?php if ( has_excerpt() ) : ?>
						<div class="excerpt" itemprop="description">
							<?php the_excerpt(); ?>
						</div><!-- //.excerpt -->
					<?php endif; ?>

					<?php get_template_part( 'parts/alert', 'old' ) ?>

					<div class="post-content clearfix" itemprop="articleBody">
						<?php the_content() ?>
					</div><!-- //.post-content -->


					<?php if ( is_singular( 'announcement' ) ) : ?>
						<?php get_template_part( 'parts/meta', 'announcement' ); ?>
					<?php endif; ?>

					<?php wp_link_pages( [
						'before'      => '<div class="row"><p class="link-pages clrB">ページ: ',
						'after'       => '</p></div>',
						'link_before' => '<span>',
						'link_after'  => '</span>',
					] ); ?>

					<?php if ( false !== array_search( get_post_type(), [ 'faq', 'anpi', 'announcement' ] ) ) : ?>

						<h2><i class="icon-vcard"></i> 著者情報</h2>
						<?php get_template_part( 'parts/author' ) ?>

					<?php endif; ?>


					<?php get_template_part( 'parts/share' ) ?>

					<?php get_template_part( 'parts/pager' ) ?>

					<div class="more">
						<?php if ( post_type_supports( get_post_type(), 'comments' ) ) : ?>
							<?php comments_template() ?>
						<?php endif; ?>
					</div>

				</article><!-- //.single-container -->

			<?php endwhile; endif; ?>

			<?php get_sidebar() ?>

		</div><!-- //.row -->

	</div><!-- //.container -->

<?php get_footer();
