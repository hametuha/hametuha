<?php get_header() ?>

<?php get_header('breadcrumb') ?>

<div class="container archive">

    <div class="row row-offcanvas row-offcanvas-right">

        <div class="col-xs-12 col-sm-9 main-container">

            <?php if( is_singular('faq') || is_tax('faq_cat') || is_post_type_archive('faq') ): ?>
                <?php get_template_part('parts/jumbotron', 'help'); ?>
            <?php endif; ?>

            <?php if( is_post_type_archive('anpi') || is_tax('anpi_cat') ): ?>
                <?php get_template_part('parts/jumbotron', 'anpi');?>
            <?php endif; ?>

            <?php if( is_post_type_archive('announcement') ): ?>
                <?php get_template_part('parts/jumbotron', 'announcement');?>
            <?php endif; ?>


            <?php if( is_tax('topic') || is_post_type_archive('thread') ): ?>
                <?php get_template_part('parts/jumbotron', 'thread') ?>
            <?php endif; ?>

            <?php if( is_author() ): ?>
                <?php get_template_part('parts/author') ?>
            <?php endif; ?>

            <?php if( is_ranking() ): ?>
                <?php get_template_part('parts/ranking') ?>
            <?php else: ?>
                <div class="archive-meta">
                    <h1>
                        <?php get_template_part('parts/h1'); ?>
                        <span class="label label-default"><?php echo number_format_i18n(loop_count()); ?>件</span>
                    </h1>

                    <div class="desc">
                        <?php get_template_part('parts/meta-desc'); ?>
                    </div>

                    <?php if( hametuha_is_profile_page() ): ?>
                        <?php get_template_part('parts/search', 'author') ?>
                    <?php endif; ?>

                    <?php if( have_posts() ): ?>
                        <?php /* get_template_part('parts/sort-order') */ ?>
                    <?php endif; ?>
                </div>

            <?php endif; ?>




            <?php
                if( is_singular('series') ){
                    $query = new WP_Query(array(
                        'post_type' => 'post',
                        'post_status' => 'publish',
                        'post_parent' => get_the_ID(),
                        'paged' => max(1, intval(get_query_var('paged')))
                    ));
                }else{
                    global $wp_query;
                    $query = $wp_query;
                }
                if( $query->have_posts() ): ?>

            <ol class="archive-container media-list">
            <?php
                $counter = 0;
                while( $query->have_posts() ){
                    $query->the_post();
                    $counter++;
                    $even = ($counter % 2 == 0) ? ' even' : ' odd';
                    if( is_ranking() ){
                        get_template_part('parts/loop', 'ranking');
                    }else{
                        get_template_part('parts/loop', get_post_type());
                    }
                }
            ?>
            </ol>

            <?php if( is_tax('topic') ): ?>

                <?php get_template_part('parts/nav', 'thread') ?>

            <?php endif; ?>

            <?php wp_pagenavi(array('query' => $query)); ?>

            <?php else: ?>

            <div class="nocontents-found alert alert-warning">
                <p>該当するコンテンツがありませんでした。以下の方法をお試しください。</p>
                <ul>
                    <li>検索ワードを変えてみる</li>
                    <li>カテゴリー、タグから探す</li>
                    <li>検索ワードの数を減らして、絞り込み検索と組み合せる</li>
                </ul>
                <p>改善要望などありましたら、<a class="alert-link" href="<?php echo home_url('/inquiry/'); ?>">お問い合わせ</a>からお願いいたします。</p>
            </div>

            <?php endif; wp_reset_postdata(); ?>

            <?php if( is_ranking() ): ?>
                <?php get_template_part('parts/ranking', 'calendar') ?>
            <?php elseif( !hametuha_is_profile_page() ): ?>
                <?php get_search_form() ?>
            <?php endif; ?>

        </div><!-- //.main-container -->

        <?php contextual_sidebar() ?>

    </div><!-- // .offcanvas -->

</div><!-- //.container -->

<?php get_footer(); ?>