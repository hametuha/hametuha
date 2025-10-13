<?php
/**
 * スレッドの一覧とトップページ
 *
 * @feature-group thread
 */
get_template_part( 'templates/thread/header-thread' );
?>

<div class="container archive">

	<div class="row">

		<div class="col-12 col-md-9 main-container">

			<?php get_template_part( 'parts/jumbotron', 'thread' ); ?>

			<div class="topic-container card mb-5">

				<div class="card-header">
					<h2 class="card-title d-flex justify-content-between align-items-center">
						<span>
						<?php
						if ( is_search() ) {
							printf( '「%s」の検索結果', get_search_query() );
						} elseif ( is_tax( 'topic' ) ) {
							echo 'トピック: ' . esc_html( get_queried_object()->name );
						} else {
							echo 'スレッド一覧';
						}
						?>
						</span>
						<?php global $wp_query; ?>
						<span class="badge bg-secondary"><?php echo number_format( $wp_query->found_posts ); ?></span>
					</h2>
				</div><!-- //.card-header -->

				<?php if ( is_tax( 'topic' ) ) : ?>
				<div class="card-body text-muted">
					<?php echo esc_html( get_queried_object()->description ); ?>
				</div>
				<?php endif; ?>

				<?php if ( have_posts() ) : ?>
					<div class="list-group">
						<?php
						while ( have_posts() ) :
							the_post();
							?>
							<a class="list-group-item list-group-item-action d-flex justify-content-between align-items-start" href="<?php the_permalink(); ?>">
								<div class="flex-grow-1">
								<?php echo get_avatar( get_the_author_meta( 'ID' ), 32 ); ?>
								<?php if ( 'private' == get_post_status() ) : ?>
									<i class="fa fa-lock text-warning"></i>
								<?php endif; ?>
								<?php if ( function_exists( 'hamethread_is_resolved' ) && hamethread_is_resolved() ) : ?>
									<i class="fa fa-check-circle text-success"></i>
								<?php endif; ?>
								<?php the_title(); ?>
								<?php if ( hamethread_recently_commented() || is_new_post() ) : ?>
									<span class="badge bg-warning">New!</span>
								<?php endif; ?>
								<small class="date">
									（<?php the_author(); ?>, <?php echo hametuha_passed_time( $post->post_date ); ?>）
								</small>
								</div>
								<span class="badge bg-secondary rounded-pill"><?php echo get_comments_number(); ?></span>
							</a>
						<?php endwhile; ?>
					</div>
				<?php endif; ?>

			</div><!-- //.topic-container -->

			<?php wp_pagenavi(); ?>

			<?php get_template_part( 'templates/thread/thread-block' ); ?>


		</div><!-- //.main-container -->

		<?php get_template_part( 'templates/thread/sidebar-thread' ); ?>

	</div><!-- // .offcanvas -->

</div><!-- //.container -->

<?php get_footer( 'books' ); ?>

<?php get_footer(); ?>
