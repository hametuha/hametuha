<?php get_header(); ?>

<?php get_header('breadcrumb') ?>

<div class="container single">

    <div class="row row-offcanvas row-offcanvas-right">
        <?php if(have_posts()): while(have_posts()): the_post(); ?>

        <article itemscope itemtype="http://schema.org/Article" <?php post_class('col-xs-12 col-sm-9 main-container')?>>

            <?php if( get_post_type() == 'faq' ): ?>
                <?php get_template_part('parts/jumbotron', 'help'); ?>
            <?php elseif( !is_page() ) : ?>
                <?php get_template_part('parts/bar', 'posttype') ?>
            <?php endif; ?>

            <!-- post thumbnail -->
            <?php if( has_post_thumbnail() ): ?>
                <div class="post-title-thumbnail row">
                    <?php the_post_thumbnail('large', array('itemprop' => 'image')); ?>
                </div>
            <?php endif; ?>

            <!-- title -->
            <div class="page-header">

                <h1 class="post-title" itemprop="name">
                    <?php the_title(); ?>
                </h1>

            </div><!-- //.page-header -->


            <!-- Meta data -->
            <div <?php post_class('post-meta')?>>

                <?php get_template_part('parts/metadata') ?>

            </div><!-- //.post-meta -->


            <?php if(has_excerpt()): ?>
                <div class="excerpt">
                    <?php the_excerpt(); ?>
                </div><!-- //.excerpt -->
            <?php endif; ?>

            <?php if( get_post_type() == 'announcement' ):?>
                <?php get_template_part('parts/meta', 'announcement'); ?>
                <?php get_template_part('parts/table', 'ticket'); ?>
            <?php endif; ?>



            <div class="post-content clearfix" itemprop="articleBody">
                <?php the_content(); ?>
            </div><!-- //.post-content -->


            <?php wp_link_pages(array('before' => '<div class="row"><p class="link-pages clrB">ページ: ', 'after' => '</p></div>', 'link_before' => '<span>', 'link_after' => '</span>')); ?>

            <?php if( get_post_type() == 'announcement' ):?>
                <?php get_template_part('parts/table', 'ticket'); ?>
            <?php endif; ?>

            <?php if( false !== array_search(get_post_type(), ['anpi', 'announcement']) ): ?>

            <h2><i class="icon-vcard"></i> 著者情報</h2>
            <div class="row">
                <?php get_template_part('parts/author') ?>
            </div>

            <?php endif; ?>


            <?php get_template_part('parts/share') ?>

            <?php get_template_part('parts/pager') ?>

            <div class="more">
                <?php if( post_type_supports(get_post_type(), 'comments') ): ?>
                    <?php comments_template() ?>
                <?php endif; ?>
            </div>

        </article><!-- //.single-container -->

        <?php endwhile; endif; ?>

        <?php contextual_sidebar() ?>

    </div><!-- //.row-offcanvas -->
</div><!-- //.container -->

<?php get_footer(); ?>