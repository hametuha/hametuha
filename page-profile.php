<?php
/*
 * Template Name: プロフィール
 */

?>
<?php get_header(); ?>

<?php get_header( 'breadcrumb' ) ?>

	<div class="container profile-container">

		<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
			<article>

				<div class="page-header">

					<h1 class="post-title text-center" itemprop="name">
						<?php the_title(); ?>
					</h1>

				</div><!-- //.page-header -->

				<?php the_content() ?>

			</article>

		<?php endwhile; endif; ?>

	</div><!-- //.container -->

<?php get_footer(); ?>

