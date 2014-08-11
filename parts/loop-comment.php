<li <?php post_class('media') ?>>

    <a class="pull-left comment-face" href="<?= get_comment_link($post->ID) ?>">
        <?= get_avatar(get_the_author_meta('ID'), 120) ?>
    </a>

    <div class="media-body">

        <!-- Title -->
        <h2 class="comment-title">
            <a href="<?= get_comment_link($post->ID) ?>"><?php the_title(); ?></a> <small>へのコメント <i class="icon-bubble"></i></small>
        </h2>

        <!-- Post Data -->
        <ul class="list-inline">
            <li>
                <span class="label label-info"><?= get_post_type_object(get_post_type($post->post_parent))->labels->name ?></span>
            </li>
            <li class="author-info">
                <?php the_author(); ?>
            </li>
            <li class="date">
                <i class="icon-calendar2"></i> <?= hametuha_passed_time($post->post_date) ?>
                <?php if( is_recent_date($post->post_date, 3) ): ?>
                    <span class="label label-danger">New!</span>
                <?php endif; ?>
            </li>
        </ul>

        <!-- Excerpt -->
        <div class="archive-excerpt">
            <p class="text-muted"><?= trim_long_sentence(strip_tags($post->post_content), 200); ?></p>
        </div>


    </div>
</li>
