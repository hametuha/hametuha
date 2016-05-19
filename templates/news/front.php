<?php get_header() ?>

<div class="news-eyecatch">
	<h1>
		<img src="<?= get_template_directory_uri() ?>/assets/img/jumbotron/hamenew.jpg"
	         alt="<?= esc_attr( hamenew_copy() ) ?>">
	</h1>
</div><!-- //.news-eyecatch -->

<div class="container archive">

	<div class="row">


		<div class="col-xs-12 col-md-9 main-container">

			<div class="archive-meta">
				<div class="desc">
					<?php get_template_part( 'parts/meta-desc' ); ?>
				</div>

			</div>

			<ul class="nav nav-pills nav-justified nav-hamenew">
				<?php foreach ( get_terms( 'genre', [ 'parent' => 0 ] ) as $term ) : ?>
				<li>
					<a href="<?= get_term_link( $term ) ?>"><?= esc_html( $term->name ) ?></a>
				</li>
				<?php endforeach; ?>
			</ul>

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
