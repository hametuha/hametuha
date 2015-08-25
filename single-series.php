<?php get_header() ?>

<?php get_header( 'breadcrumb' ) ?>

<div class="series__wrap" itemscope itemtype="http://schema.org/Book" >

	<?php the_post(); $series = \Hametuha\Model\Series::get_instance(); ?>

	<div class="series__row--cover">
		<div class="container series__inner" id="series-<?php the_ID() ?>">

			<meta itemprop="bookFormat" content="EBook">

			<div class="row meta--series">
				<?php if ( has_post_thumbnail() ) : ?>
					<div class="col-xs-12 col-sm-3 meta__thumbnail">
						<?php the_post_thumbnail( 'medium', [
							'itemprop' => 'image',
						] ) ?>
					</div>
				<?php endif; ?>

				<div class="col-xs-12<?= has_post_thumbnail() ? ' col-sm-9' : '' ?>">

					<!-- title -->
					<div class="page-header">
						<h1 class="post-title post-title--series">
							<span itemprop="name"><?php the_title(); ?></span>
							<?php if ( ( $subtitle = $series->get_subtitle( $post->ID ) ) ) : ?>
								<br /><small itemprop="headline">
									<?= esc_html( $subtitle ) ?>
								</small>
							<?php endif; ?>
						</h1>
					</div>

					<!-- Meta data -->
					<div <?php post_class( 'post-meta' ) ?>>
						<?php get_template_part( 'parts/meta', 'single' ); ?>
					</div>
					<!-- //.post-meta -->

					<?php if ( has_excerpt() ) : ?>
						<div class="excerpt" itemprop="description">
							<?php the_excerpt(); ?>
						</div><!-- //.excerpt -->
					<?php endif; ?>

				</div>
			</div>

			<?php get_template_part( 'parts/alert', 'kdp' ) ?>

			<?php get_template_part( 'parts/share' ) ?>

		</div>
	</div><!-- //series__row--cover -->

	<div class="series_row--children">

		<div class="container series__inner">

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

			<?php endif; wp_reset_postdata(); ?>
		</div><!-- //.container -->

	</div><!-- series_row--children -->

</div><!-- //.series__wrap -->

<?php get_footer(); ?>
