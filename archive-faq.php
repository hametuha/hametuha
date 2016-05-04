<?php get_header() ?>

<?php get_header( 'breadcrumb' ) ?>

    <div class="container archive">

        <div class="row row-offcanvas row-offcanvas-right">

            <div class="col-xs-12 col-sm-9 main-container">

                <?php get_template_part( 'parts/jumbotron', 'help' ); ?>

                <div class="faq__wrapper">

                    <dl class="faq__list">

                        <?php foreach ( get_terms( 'faq_cat' ) as $term ) : ?>

                            <dt class="faq__cat">
                                <span class="faq__term"><?= esc_html( $term->name ) ?></span>
                                <?php if ( $term->description ) : ?>
                                <small class="faq__desc">
                                    <?= nl2br( esc_html( $term->description ) ) ?>
                                </small>
                                <?php endif; ?>
                            </dt>
                            <dd class="faq__content">
                                <ul class="faq__items">
                                    <?php
                                    foreach ( get_posts( [
                                        'post_type' => 'faq',
                                        'post_status' => 'publish',
                                        'orderby' => [ 'date' => 'DESC' ],
                                        'posts_per_page' => 3,
                                        'tax_query' => [
                                            [
                                                'taxonomy' => 'faq_cat',
                                                'terms' => $term->term_id,
                                                'field' => 'id',
                                            ],
                                        ],
                                    ] ) as $faq ) : ?>
                                        <li class="faq__item">
                                            <a href="<?= get_permalink( $faq ) ?>" class="faq__link">
                                                <?= get_the_title( $faq ) ?> <i class="icon-arrow-right2"></i>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                <a class="btn btn-default btn-block faq__btn" href="<?= get_term_link( $term ) ?>">
                                    もっと見る
                                </a>
                            </dd>

                        <?php endforeach; ?>

                    </dl>

                    <?php get_template_part( 'parts/nav', 'faq' ) ?>

                    <?php get_search_form(); ?>
                </div>

            </div>
            <!-- //.main-container -->

            <?php get_sidebar() ?>

        </div>
        <!-- // .offcanvas -->

    </div><!-- //.container -->

<?php get_footer(); ?>