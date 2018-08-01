<?php get_header( 'faq' ) ?>

    <div class="container archive">

        <div class="row row-offcanvas row-offcanvas-right">

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
                <?php if ( have_posts() ) : ?>
                    <ol class="archive-container media-list">
                        <?php while ( have_posts() ) : the_post(); ?>
                            <?php get_template_part( 'parts/loop', get_post_type() ) ?>
                        <?php endwhile; ?>
                    </ol>
                    <?php wp_pagenavi(); ?>
                <?php else : ?>
					<?php get_template_part( 'parts/no', 'content' ) ?>
                <?php endif; ?>
                <?php google_adsense( 'related' ) ?>
            </div>
            <!-- //.main-container -->

			<?php get_sidebar( 'faq' ) ?>

        </div>
        <!-- // .offcanvas -->

    </div><!-- //.container -->

<?php get_footer(); ?>