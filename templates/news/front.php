<?php get_header(); ?>

<?php get_template_part( 'templates/news/header', 'news' ); ?>

<div class="container archive">

	<?php get_template_part( 'templates/news/nav', 'news' ); ?>

	<div class="row">

		<div class="col-xs-12 col-md-9 main-container">


			<div class="row news-ad__archive">
				<p class="news-ad__title">Ads by Google</p>
				<?php google_adsense( 4 ); ?>
			</div>


			<?php if ( have_posts() ) : ?>
			<!-- Tab panes -->
			<ol class="archive-container media-list">
				<?php
				while ( have_posts() ) {
					the_post();
					get_template_part( 'parts/loop', get_post_type() );
				}
				?>
			</ol>
				<?php wp_pagenavi(); ?>
			<?php else : ?>
			<div class="alert alert-warning">
				該当するニュースはありませんでした。
			</div>
			<?php endif; ?>

			<div class="row">

				<div class="col-xs-12 col-sm-6 news-ad--content">
					<?php google_adsense( 4 ); ?>
					<p class="news-ad__title">Ads by Google</p>
				</div>

				<div class="col-xs-12 col-sm-6 news-related">
					<div class="fb-page" data-href="https://www.facebook.com/minico.me/" data-width="500" data-small-header="false" data-adapt-container-width="true" data-hide-cover="false" data-show-facepile="true"><div class="fb-xfbml-parse-ignore"><blockquote cite="https://www.facebook.com/minico.me/"><a href="https://www.facebook.com/minico.me/">ミニ子</a></blockquote></div></div>
				</div>

			</div>


			<?php get_search_form(); ?>

			<?php get_template_part( 'templates/news/block', 'keywords' ); ?>

			<?php get_template_part( 'parts/jumbotron', 'news' ); ?>

		</div>
		<!-- //.main-container -->

		<?php get_sidebar( 'news' ); ?>

	</div><!-- // .row -->

</div><!-- //.container -->

<?php get_footer(); ?>
