<li <?php post_class('media loop-series') ?>>

    <?php if( has_post_thumbnail() ): ?>
        <a class="pull-right" href="<?php the_permalink() ?>">
            <?= get_the_post_thumbnail(null, 'thumbnail', array('class' => 'media-object')) ?>
        </a>
    <?php endif; ?>

    <div class="media-body">

        <!-- Title -->
        <h2>
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
            <small>作品集</small>
        </h2>

        <!-- Post Data -->
        <ul class="list-inline">
            <li class="author-info">
                <?php echo get_avatar(get_the_author_meta('ID'), 40); ?>
                <?php the_author(); ?> 編
            </li>
            <li class="date">
                <i class="icon-calendar2"></i> <?= hametuha_passed_time($post->post_date) ?>
                <?php if( is_recent_date($post->post_date, 3) ): ?>
                    <span class="label label-danger">New!</span>
                <?php elseif( is_recent_date($post->post_modified, 7) ): ?>
                    <span class="label label-info">更新</span>
                <?php endif; ?>
            </li>
            <li>
                <i class="icon-books"></i> <?= number_format_i18n(get_post_children_count()); ?>作収録
            </li>
            <li class="static">
                <i class="icon-reading"></i> <?php the_post_length('全', '文字', '計測不能') ?>
            </li>
        </ul>

        <!-- Excerpt -->
        <div class="archive-excerpt">
            <p class="text-muted"><?= trim_long_sentence(get_the_excerpt(), 98); ?></p>
        </div>


    </div>
</li>
