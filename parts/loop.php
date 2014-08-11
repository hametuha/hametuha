<li <?php post_class('media') ?>>

    <?php if( has_post_thumbnail() ): ?>
        <a class="pull-right" href="<?php the_permalink() ?>">
            <?= get_the_post_thumbnail(null, 'thumbnail', array('class' => 'media-object')) ?>
        </a>
    <?php endif; ?>

    <div class="media-body">

        <!-- Title -->
        <h2>
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
            <?php if( 'post' == get_post_type() && ($terms = get_the_category()) ): ?>
                <?php foreach($terms as $category): ?>
                    <small><?= esc_html($category->name) ?></small>
                <?php endforeach; ?>
            <?php endif; ?>
        </h2>

        <!-- Post Data -->
        <ul class="list-inline">
            <?php switch(get_post_type()): case 'faq': ?>
                <?php the_terms(get_the_ID(), 'faq_cat', '<li><i class="icon-tags"></i> ', ', ', '</li>') ?>
            <?php break; case 'info': ?>
            <?php break; default: ?>
                <li class="author-info">
                    <?php echo get_avatar(get_the_author_meta('ID'), 40); ?>
                    <?php the_author_posts_link(); ?>
                </li>
            <?php break; endswitch; ?>
            <li class="date">
                <i class="icon-calendar2"></i> <?= hametuha_passed_time($post->post_date) ?>
                <?php if( is_recent_date($post->post_date, 3) ): ?>
                    <span class="label label-danger">New!</span>
                <?php elseif( is_recent_date($post->post_modified, 7) ): ?>
                    <span class="label label-info">更新</span>
                <?php endif; ?>
            </li>
            <li class="static"><i class="icon-reading"></i> <?= number_format(get_post_length()) ?>文字</li>
        </ul>

        <!-- Excerpt -->
        <div class="archive-excerpt">
            <p class="text-muted"><?= trim_long_sentence(get_the_excerpt(), 98); ?></p>
        </div>


    </div>
</li>
