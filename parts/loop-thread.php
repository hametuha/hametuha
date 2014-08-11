<li <?php post_class('media') ?>>
    <a class="pull-left" href="<?php the_permalink() ?>">
        <?= get_avatar(get_the_author_meta('ID'), 160); ?>
    </a>

    <div class="media-body">
        <h3 class="title">
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
            <small><i class="icon-calendar"></i> <?= hametuha_passed_time($post->post_date) ?></small>
        </h3>
        <ul class="list-inline">
            <?php the_terms(get_the_ID(), 'topic', '<li><i class="icon-tags"></i> ', ', ', '</li>') ?>
            <li>
                <i class="icon-user"></i> <?php the_author() ?>
            </li>
            <li>
                <i class="icon-bubbles3"></i> レス <?= ($number = get_comments_number()) ?>件
            </li>
            <li>
                <i class="icon-clock"></i> 最新レス
                <?php
                $latest = get_latest_comment_date();
                echo $latest ? mysql2date('Y/n/j', $latest) : 'なし';
                if( recently_commented() ):
                    ?>
                    <span class="label label-success">New!</span>
                <?php endif; ?>
            </li>
        </ul>

        <div class="archive-excerpt text-muted">
            <?php the_excerpt(); ?>
        </div><!-- .excerpt -->
    </div>
</li>