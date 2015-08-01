<?php get_header() ?>

<?php get_header( 'breadcrumb' ) ?>

<div class="container archive">

	<div class="row row-offcanvas row-offcanvas-right">

		<div class="col-xs-12 col-sm-9 main-container">
			<?php the_post(); ?>
			<div itemscope itemtype="http://schema.org/Book" id="series-<?php the_ID() ?>">
				<meta itemprop="bookFormat" content="EBook">
				<?php get_template_part( 'parts/meta', 'series' ); ?>
			</div>

			<div>
				<?php
				$query = new WP_Query( [
					'post_type'      => 'post',
					'post_status'    => 'publish',
					'post_parent'    => get_the_ID(),
					'posts_per_page' => - 1,
					'orderby'        => [
						'menu_order' => 'ASC',
						'date'       => 'DESC',
					],
					'paged'          => max( 1, intval( get_query_var( 'paged' ) ) ),
				] );
				if ( $query->have_posts() ) :

					?>

					<!-- Tab panes -->
					<div class="tab-content">
						<div class="tab-pane active">
							<ol class="archive-container media-list">
								<?php
								$counter = 0;
								while ( $query->have_posts() ) {
									$query->the_post();
									$counter ++;
									$even = ( 0 == $counter % 2 ) ? ' even' : ' odd';
									get_template_part( 'parts/loop', get_post_type() );
								}
								?>
							</ol>
						</div>
						<!-- //.tab-pane -->
					</div><!-- //.tab-content -->

					<?php wp_pagenavi( [ 'query' => $query ] ); ?>

				<?php else : ?>

					<?php get_template_part( 'parts/no', 'content' ) ?>

				<?php endif;
				wp_reset_postdata(); ?>

			</div>

		</div>
		<!-- //.main-container -->

		<?php contextual_sidebar() ?>

	</div>
	<!-- // .offcanvas -->

</div><!-- //.container -->

<?php get_footer(); ?>
