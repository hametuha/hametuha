<?php
/**
 * アーカイブページ
 *
 * 投稿用と
 */
get_header();
get_header( 'sub' );
get_header( 'breadcrumb' );
?>

	<div class="container archive">

		<div class="row row-offcanvas row-offcanvas-right">

			<div class="col-12 col-lg-9 order-2 main-container">


				<?php
				if ( is_singular( 'list' ) ) {
					get_template_part( 'parts/meta', 'lists' );
				} elseif ( is_post_type_archive( 'list' ) )  {
					get_template_part( 'parts/jumbotron', 'lists' );
				} elseif ( is_tax( 'campaign' ) ) {
					get_template_part( 'parts/meta', 'campaign' );
				} else {
					get_template_part( 'parts/meta', 'post' );
				}
				?>

				<div>

					<?php
					if ( is_singular( 'lists' ) ) {
						$query = new WP_Query( [
							'post_type'   => 'in_list',
							'post_status' => 'publish',
							'post_parent' => get_the_ID(),
							'paged'       => max( 1, intval( get_query_var( 'paged' ) ) ),
						] );
					} else {
						global $wp_query;
						$query = $wp_query;
					}
					if ( $query->have_posts() ) :
						get_template_part( 'parts/sort-order' );
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
										$even = ( 0 === $counter % 2 ) ? ' even' : ' odd';
										get_template_part( 'parts/loop', get_post_type() );
									}
									?>
								</ol>
							</div>
							<!-- //.tab-pane -->
						</div><!-- //.tab-content -->

						<?php
						// Load navigation
						if ( get_query_var( 'reviewed_as' ) ) {
							get_template_part( 'parts/nav', 'review' );
						}
						?>

						<?php wp_pagenavi( [ 'query' => $query ] ); ?>


					<?php else : ?>

						<?php
						if ( is_tax( 'campaign' ) ) {
							$no_slug = 'campaign';
						} else {
							$no_slug = '';
						}
						get_template_part( 'parts/no-content', $no_slug );
						?>

						<?php
					endif;
					wp_reset_postdata();
					?>

					<?php
					// Extras
					if ( is_singular( 'lists' ) || is_post_type_archive( 'lists' ) ) {
						get_template_part( 'parts/nav', 'lists' );
					} elseif ( is_tax( 'campaign' ) ) {
						get_template_part( 'parts/content-campaign', get_term_meta( get_queried_object_id(), '_is_collaboration', true ) ? 'collaboration' : '' );
					}
					// Content
					if ( ( is_category() || is_tag() || is_tax() ) && ( $content = get_term_meta( get_queried_object_id(), '_term_content', true ) ) ) {
						printf( '<div class="post-content clearfix">%s</div>', apply_filters( 'the_content', $content ) );
					}
					?>
				</div>

			</div>
			<!-- //.main-container -->

			<?php get_sidebar( 'post' ); ?>

		</div>
		<!-- // .offcanvas -->

	</div><!-- //.container -->

<?php
get_footer( 'books' );
get_footer();
