<?php
/*
 * ランキング用
 */

$rank = get_the_ranking();

?>
<li <?php post_class('media rank-list'.ranking_class($rank)) ?>>

    <?php if( 1 === $rank ): ?>
        <i class="rank-icon icon-trophy2"></i>
    <?php endif; ?>
    <span class="pull-left" >
        <i class="icon-circle2"></i>
        <strong><?= $rank ?></strong>
        <?php if( is_null($post->transition) ): ?>
            <i class="rank-status icon-new"></i>
        <?php else: ?>
            <?php switch( $post->transition ): case 0: ?>
                <i class="rank-status icon-arrow-right5"></i>
            <?php break; case -1: ?>
                <i class="rank-status icon-arrow-down-right2"></i>
            <?php break; case 1: ?>
                <i class="rank-status icon-arrow-up-right2"></i>
            <?php break; endswitch; ?>
        <?php endif; ?>
    </span>

    <div class="media-body">

        <!-- Title -->
        <h2>
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
            <?php if( 'post' == get_post_type() && ($terms = get_the_category()) ): ?>
                <?php foreach($terms as $category): ?>
                    <small><?= esc_html($category->name) ?></small>
                <?php endforeach; ?>
            <?php endif; ?>
            <?php if( current_user_can('edit_others_posts') ): ?>
                <span class="label label-default"><?= number_format($post->pv) ?>PV</span>
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
