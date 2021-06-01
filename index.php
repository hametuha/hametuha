<?php get_header(); ?>
<?php get_header( 'sub' ); ?>
<?php get_header( 'breadcrumb' ); ?>

	<div class="container archive">

		<div class="row row-offcanvas row-offcanvas-right">

			<div class="col-xs-12 col-sm-9 main-container">

				<?php
				// Jumbotron
				if ( is_tax( 'faq_cat' ) ) {
					get_template_part( 'parts/jumbotron', 'help' );
				} elseif ( 'kdp' == get_query_var( 'meta_filter' ) ) {
					get_template_part( 'parts/jumbotron', 'kdp' );
				} elseif ( is_post_type_archive( 'anpi' ) || is_tax( 'anpi_cat' ) ) {
					get_template_part( 'parts/jumbotron', 'anpi' );
				} elseif ( is_post_type_archive( 'announcement' ) ) {
					get_template_part( 'parts/jumbotron', 'announcement' );
				} elseif ( is_tax( 'topic' ) || is_post_type_archive( 'thread' ) ) {
					get_template_part( 'parts/jumbotron', 'thread' );
				} elseif ( is_post_type_archive( 'lists' ) ) {
					get_template_part( 'parts/jumbotron', 'lists' );
				} elseif ( is_ranking() ) {
					get_template_part( 'parts/jumbotron', 'ranking' );
				} elseif ( is_post_type_archive( 'ideas' ) ) {
					get_template_part( 'parts/jumbotron', 'ideas' );
				}
				?>

				<?php if ( is_author() ) : ?>
					<?php get_template_part( 'parts/author' ); ?>
				<?php endif; ?>


				<?php
				if ( is_singular( 'lists' ) ) {
					get_template_part( 'parts/meta', 'lists' );
				} else {
					?>
					<div class="archive-meta">
						<h1>
							<?php get_template_part( 'parts/h1' ); ?>
							<span class="label label-default"><?php echo number_format_i18n( loop_count() ); ?>件</span>
						</h1>

						<div class="desc">
							<?php get_template_part( 'parts/meta-desc' ); ?>
						</div>

						<?php if ( hametuha_is_profile_page() ) : ?>
							<?php get_template_part( 'parts/search', 'author' ); ?>
						<?php endif; ?>


					</div>
				<?php } ?>

				<?php
				if ( is_tax( 'campaign' ) ) {
					get_template_part( 'parts/meta', 'campaign' );
				}
				?>
				<div>

					<?php
					if ( is_singular( 'lists' ) ) {
						$query = new WP_Query(
							[
								'post_type'   => 'in_list',
								'post_status' => 'publish',
								'post_parent' => get_the_ID(),
								'paged'       => max( 1, intval( get_query_var( 'paged' ) ) ),
							]
						);
					} else {
						global $wp_query;
						$query = $wp_query;
					}
					if ( $query->have_posts() ) :

						if ( ! is_ranking() && ! get_query_var( 'reviewed_as' ) && ! hametuha_is_profile_page() ) {
							get_template_part( 'parts/sort-order' );
						}

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
										if ( is_ranking() ) {
											get_template_part( 'parts/loop', 'ranking' );
										} else {
											get_template_part( 'parts/loop', get_post_type() );
										}
									}
									?>
								</ol>
							</div>
							<!-- //.tab-pane -->
						</div><!-- //.tab-content -->

						<?php
						// Load navigation
						if ( is_tax( 'topic' ) ) {
							get_template_part( 'parts/nav', 'thread' );
						} elseif ( get_query_var( 'reviewed_as' ) ) {
							get_template_part( 'parts/nav', 'review' );
						} elseif ( ( ( ! is_ranking() || ! get_query_var( 'reviewed_as' ) ) && is_home() ) || is_post_type_archive( 'post' ) || is_category() || is_tag() || is_search() ) {
							get_template_part( 'parts/nav' );
						}
						?>

						<?php wp_pagenavi( [ 'query' => $query ] ); ?>


					<?php else : ?>

						<?php get_template_part( 'parts/no', 'content' ); ?>

						<?php
					endif;
					wp_reset_postdata();
					?>

					<?php
					// Extras
					if ( is_ranking() ) {
						get_template_part( 'parts/ranking', 'calendar' );
					} elseif ( is_singular( 'lists' ) || is_post_type_archive( 'lists' ) ) {
						get_template_part( 'parts/nav', 'lists' );
					} elseif ( is_tax( 'faq_cat' ) ) {
						get_template_part( 'parts/nav', 'faq' );
						get_search_form();
					} elseif ( is_tax( 'campaign' ) ) {
						get_template_part( 'parts/ranking', 'campaign' );
					} elseif ( ! hametuha_is_profile_page() ) {
						get_search_form();
					}
					// Content
					if ( ( is_category() || is_tag() || is_tax() ) && ( $content = get_term_meta( get_queried_object_id(), '_term_content', true ) ) ) {
						printf( '<div class="post-content clearfix">%s</div>', apply_filters( 'the_content', $content ) );
					}
					?>
				</div>

			</div>
			<!-- //.main-container -->

			<?php get_sidebar(); ?>

		</div>
		<!-- // .offcanvas -->

	</div><!-- //.container -->

<?php get_footer(); ?>
