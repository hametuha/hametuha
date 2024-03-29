<?php get_template_part( 'templates/thread/header-thread' ); ?>

	<div class="container single">

		<div class="row row-offcanvas row-offcanvas-right">

			<?php
			if ( have_posts() ) :
				while ( have_posts() ) :
					the_post();
					?>

				<article itemscope
						 itemtype="http://schema.org/Question" <?php post_class( 'col-xs-12 col-sm-9 main-container' ); ?>>

					<div class="page-header thread-header clearfix">

						<div class="thread-info col-sm-3 col-xs-12 text-center clearfix">
							<div class="col-xs-6 col-sm-12">
								<p>
									<?php echo get_avatar( get_the_author_meta( 'ID' ), 160, '', esc_attr( get_the_author() ), [ 'extra_attr' => 'itemprop="image"' ] ); ?>
								</p>
								<p class="author">
									<small class="text-muted"><?php echo hametuha_user_role( get_the_author_meta( 'ID' ) ); ?></small>
									<br/>
									<span itemprop="author"><?php the_author(); ?></span>
								</p>
							</div>
							<div class="col-xs-6 col-sm-12">
								<p>
									<strong><i class="icon-stack-list"></i> スレ立て</strong><br/>
									<span><?php echo number_format_i18n( hamethread_get_author_thread_count( get_the_author_meta( 'ID' ) ) ); ?>
										件</span>
								</p>
								<p>
									<strong><i class="icon-bubble"></i> コメント</strong><br/>
									<span><?php echo number_format_i18n( hamethread_get_author_response_count( get_the_author_meta( 'ID' ) ) ); ?>
										件</span>
								</p>
								<?php if ( user_can( get_the_author_meta( 'ID' ), 'edit_posts' ) ) : ?>
									<p>
										<a class="btn btn-info btn-block"
										   href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) ); ?>">
											投稿一覧
										</a>
									</p>
								<?php endif; ?>
							</div>
						</div><!-- //.thread-info -->


						<div class="thread-body col-sm-9 col-xs-12">

							<h1>
								<?php
								$notices = [];
								if ( 'private' === get_post_status() ) {
									$notices[] = '<small class="text-warning"><i class="fa fa-lock text-warning"></i> 非公開</small>';
								}
								if ( function_exists( 'hamethread_is_resolved' ) && hamethread_is_resolved() ) {
									$notices[] = '<small class="text-success"><i class="fa fa-check-circle"></i> 解決済み</small>';
								}
								if ( $notices ) {
									$notices[] = '<br />';
									echo implode( ' ', $notices );
								}
								?>
								<span class="thread-body-title" itemprop="name">
									<?php the_title(); ?>
								</span>
							</h1>

											<?php get_template_part( 'parts/meta', 'single' ); ?>

							<div class="thread-inner" itemprop="text">
								<?php the_content(); ?>
							</div><!-- //.thread-inner -->


						</div><!-- //.thread-body -->

					</div><!-- //.thread-header -->


					<div class="more">
										<?php comments_template(); ?>
					</div>

									<?php get_template_part( 'templates/thread/thread-block' ); ?>

				</article><!-- //.single-container -->

							<?php
			endwhile;
endif;
			?>

			<?php get_template_part( 'templates/thread/sidebar-thread' ); ?>

		</div><!-- //.row-offcanvas -->
	</div><!-- //.container -->

<?php get_footer( 'books' ); ?>

<?php get_footer(); ?>
