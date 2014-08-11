
<div <?php post_class('post-meta post-meta-single clearfix')?>>
    <h1 class="mincho">
        <span class="series"><a href="<?php echo get_post_type_archive_link('series'); ?>">作品集</a></span>
        <?php the_title(); ?>
    </h1>
    <p class="author">
        編集者： <a href="#post-author"><?php the_author(); ?></a>
    </p>
    <p class="genre">
        <span class="category">全<?php echo number_format_i18n(count($query->posts)); ?>作品</span>
    </p>

    <?php if(has_post_thumbnail()): ?>
        <?php the_post_thumbnail(); ?>
    <?php elseif(has_pixiv()): ?>
        <?php pixiv_output(); ?>
    <?php else: ?>
        <img class="attachment-post-thumbnail" width="300" height="400" alt="<?php the_title(); ?>" src="<?php echo get_template_directory_uri(); ?>/img/covers/default-300x400.jpg" />
    <?php endif; ?>

    <?php if(has_excerpt()): ?>
        <div class="desc clearfix clrB">
            <?php the_excerpt(); ?>
        </div>
    <?php endif; ?>
</div><!-- //.post-meta-single -->

<div class="post-content single-post-content mincho clearfix">
    <?php
    global $post;
    if(!empty($post->post_content)){
        the_content();
    }else{
        'なにも書いてない';
    }
    //TODO: 作品集をePub書き出しする場合にどうするか検討
    wp_link_pages(array('before' => '<p class="link-pages clrB">ページ: ', 'after' => '</p>', 'link_before' => '<span>', 'link_after' => '</span>'));
    ?>
</div><!-- //.single-post-content -->