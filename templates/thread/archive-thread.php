<?php get_template_part( 'templates/thread/header-thread' ) ?>

<div class="container archive">

    <div class="row">

        <div class="col-xs-12 col-sm-9 main-container">

			<?php get_template_part( 'parts/jumbotron', 'thread' ) ?>

            <div class="topic-container panel panel-default">

                <div class="panel-heading">
                    <h2 class="panel-title">
                        <?php
                        if ( is_search() ) {
                            printf( '「%s」の検索結果', get_search_query() );
                        } elseif ( is_tax( 'topic' ) ) {
                            echo 'トピック: '. esc_html( get_queried_object()->name );
                        } else {
                            echo 'スレッド一覧';
                        }
                        global $wp_query;
                        ?>
                        <span class="badge"><?= number_format( $wp_query->found_posts ); ?></span>
                    </h2>
                </div><!-- //.panel-title -->

                <?php if (  is_tax( 'topic' ) ) : ?>
                <div class="panel-body text-muted">
					<?= esc_html( get_queried_object()->description ); ?>
                </div>
                <?php endif; ?>

				<?php if ( have_posts() ) : ?>
                    <div class="list-group">
						<?php while ( have_posts() ) : the_post(); ?>
                            <a class="list-group-item" href="<?php the_permalink(); ?>">
                                <span class="badge"><?= get_comments_number() ?></span>
								<?= get_avatar( get_the_author_meta( 'ID' ), 32 ); ?>
								<?php the_title(); ?>
								<?php if ( hamethread_recently_commented() || is_new_post() ): ?>
                                    <span class="label label-warning">New!</span>
								<?php endif; ?>
                                <small class="date">
                                    （<?php the_author() ?>, <?= hametuha_passed_time( $post->post_date ); ?>）
                                </small>
                            </a>
						<?php endwhile; ?>
                    </div>
				<?php endif; ?>

            </div><!-- //.topic-container -->

            <?php wp_pagenavi() ?>

			<?php get_template_part( 'templates/thread/thread-block' ) ?>


        </div><!-- //.main-container -->

		<?php get_template_part( 'templates/thread/sidebar-thread' ) ?>

    </div><!-- // .offcanvas -->

</div><!-- //.container -->

<?php get_footer(); ?>

