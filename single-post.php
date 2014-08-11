<?php get_header(); ?>

<?php if(have_posts()): while(have_posts()): the_post(); ?>

<article id="viewing-content" <?php post_class() ?> itemscope itemtype="http://schema.org/Article">

    <div id="content-wrapper">
        <?php if( has_post_thumbnail() ): ?>

            <div class="single-post-thumbnail text-center">
                <?php the_post_thumbnail('large', array('item-prop' => 'image')); ?>
            </div>

        <?php elseif( has_pixiv() ): ?>

            <div class="single-post-thumbnail pixiv text-center">
                <?php pixiv_output(); ?>
            </div>

        <?php endif; ?>

        <div class="work-wrapper container">

            <div class="work-meta row">

                <div class="inner">

                    <h1 itemprop="name"><?php the_title(); ?></h1>

                    <?php the_series('<p class="series">', '</p>'); ?>

                    <p class="author">
                        <a href="#post-author"><?php the_author(); ?></a>
                    </p>

                    <p class="genre">
                        <?php the_category(' ');?>
                    </p>

                    <p class="length">
                        <?php the_post_length('<span>', '</span>', '-');?>文字
                    </p>

                    <?php if( has_excerpt() ): ?>
                        <div class="desc">
                            <?php the_excerpt(); ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div><!-- //.post-meta-single -->


            <div class="work-content row" itemprop="text">
                <?php the_content(); ?>

                <?php if( is_last_page() ):?>
                    <p id="work-end-ranker" class="text-center" data-post="<?php the_ID() ?>"><i class="icon-ha"></i></p>
                <?php endif; ?>

                <?php wp_link_pages(array('before' => '<p class="link-pages">ページ: ', 'after' => '</p>', 'link_before' => '<span>', 'link_after' => '</span>')); ?>
            </div><!-- //.single-post-content -->

            <p class="text-center pub-date">
                <span itemprop="dateCreated"><?php the_time('Y年n月j日') ?></span>公開
            </p>

            <?php if( is_series() ): ?>
                <p class="series-pager-title text-center">
                    作品集『<?php the_series(); ?>』より
                </p>
                <ul class="pager post-pager">
                    <?php prev_series_link('<li class="previous">'); ?>
                    <?php next_series_link('<li class="next">'); ?>
                </ul>
            <?php endif; ?>

            <div id="single-post-footernote" class="row">
                &copy; <span itemprop="copyrightYear"><?php the_time('Y'); ?></span> <?php the_author(); ?>
            </div>

            <div id="post-author" class="row author-container">
                <?php get_template_part('parts/author') ?>
            </div>

        </div><!-- // .work-wrapper -->

    </div><!-- //#content-wrapper -->

    <div id="reading-nav">
        <div class="container">
            <div id="slider"></div>
            <a href="#" class="reset-viewer"><i class="icon-close3"></i></a>
        </div>
    </div>

    <div id="finish-wrapper" class="overlay-container">
        <div class="container">
            <div id="post-share" class="share-panel text-center">
                <h4>この作品をシェアする</h4>
                <?php hametuha_share( get_the_title(), get_permalink() ) ; ?>
                <div class="input-group">
                    <span class="input-group-addon">URL</span>
                    <input class="form-control" id="post-short-link" type="text" value="<?= esc_attr(wp_get_shortlink()); ?>" onclick="this.select();" />
                </div>
                <?php if( get_current_user_id() == get_the_author_meta('ID') ):?>
                    <div class="alert alert-info">
                        これはあなたの作品です。積極的に宣伝し、たくさんの読者に読んでもらいましょう。
                        いいねやTwitterでの宣伝など、周囲に疎まれる限界まで宣伝してください。
                    </div>
                <?php endif; ?>
            </div><!-- #post-share -->

            <div>
                <?php Hametuha\Ajax\Feedback::form('parts/feedback', 'you', ['id' => 'review-form']) ?>
            </div>
        </div>
    </div>


    <div id="reviews-wrapper" class="overlay-container">
        <div class="container">
            <?php Hametuha\Ajax\Feedback::all_review(get_the_ID()) ?>
        </div>
    </div>

    <div id="tags-wrapper" class="overlay-container">
        <div id="post-tags" class="container">
            <?php Hametuha\Rest\UserTag::view('parts/feedback', 'tag') ?>
        </div><!-- //#post-tags -->
    </div>

    <div id="comments-wrapper" class="overlay-container">
        <div id="post-comment" class="container">
            <?php comments_template(); ?>
        </div>
    </div>


    <a class="overlay-close reset-viewer" href="#">
        <i class="icon-esc"></i> 作品に戻る
    </a>

</article>



<?php endwhile; endif; ?>

<footer id="footer-single">
    <nav class="container">
        <ul class="clearfix">
            <li>
                <a href="#reading-nav">
                    <i class="icon-book"></i><br />
                    <span>移動</span>
                </a>
            </li>
            <li class="finished-container">
                <a href="#finish-wrapper">
                    <i class="icon-reading"></i><br />
                    <span>読了</span>
                </a>
            </li>
            <li>
                <a href="#reviews-wrapper">
                    <i class="icon-star6"></i><br />
                    <span>レビュー</span>
                </a>
            </li>
            <li>
                <a href="#comments-wrapper">
                    <i class="icon-bubbles"></i><br />
                    <span>コメント</span>
                </a>
            </li>
            <li>
                <a href="#tags-wrapper">
                    <i class="icon-tags"></i><br />
                    <span>タグ</span>
                </a>
            </li>
        </ul>
    </nav><!-- //.container -->
</footer>

<?php get_footer('single'); ?>