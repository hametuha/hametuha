<?php
/**
 * ニュースアーカイブテンプレート
 *
 * @feature-group news
 */
get_header();
get_header( 'breadcrumb' );
get_template_part( 'templates/news/header', 'news' );
?>

<div class="container archive mt-5">

	<?php get_template_part( 'templates/news/nav', 'news' ); ?>

	<div class="row">

		<div class="col-12 col-md-9 main-container">

			<!-- Tab panes -->
			<ol class="archive-container media-list row">
				<?php
				$counter = 0;
				while ( have_posts() ) {
					++$counter;
					$type = 'normal';
					if ( is_hamenew( 'front' ) && 6 >= $counter ) {
						$type = 'card';
					}
					the_post();
					get_template_part( 'parts/loop', get_post_type(), [
						'type' => $type,
					] );
					if ( 6 === $counter ) {
						echo '<li class="news-list__item col-12">';
						google_adsense( 4 );
						echo '<p class="news-ad__title mb-2">Ads by Google</p>';
						echo '</li>';
					}
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

			<?php get_template_part( 'templates/news/jumbotron' ); ?>

		</div>
		<!-- //.main-container -->

		<?php get_sidebar( 'news' ); ?>

	</div><!-- // .row -->

</div><!-- //.container -->

<?php
get_footer( 'ebooks' );
get_footer( 'books' );
get_footer();
