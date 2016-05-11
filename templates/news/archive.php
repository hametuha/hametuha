<?php get_header() ?>

<?php get_header( 'breadcrumb' ) ?>

	<div class="container archive">

		<div class="row">

			<div class="col-xs-12 col-sm-9 main-container">

				<div class="archive-meta">
					<h1>
						<?php get_template_part( 'parts/h1' ); ?>
						<span class="label label-default"><?php echo number_format_i18n( loop_count() ); ?>件</span>
					</h1>

					<div class="desc">
						<?php get_template_part( 'parts/meta-desc' ); ?>
					</div>

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

				<?php wp_pagenavi(); ?>

				<?php get_search_form(); ?>

			</div>
			<!-- //.main-container -->

			<?php get_sidebar( 'news' ) ?>

		</div><!-- // .row -->

		<?php get_template_part( 'parts/jumbotron', 'news' ) ?>

	</div><!-- //.container -->

<?php get_footer(); ?>
