<ul class="metadata list-inline">
    <!-- taxonomies -->
    <?php
    $before = '<li class="genre"><i class="icon-tags"></i> ';
    $after = '</li>';
    switch( get_post_type() ){
        case 'thread':
            the_terms(get_the_ID(), 'topic', $before, $after);
            break;
        case 'faq':
            the_terms(get_the_ID(), 'faq_cat', $before, $after);
            break;
        case 'anpi':
            the_terms(get_the_ID(), 'anpi_cat', $before, $after);
            break;
    }
    ?>
    <!-- Date -->
    <li class="date">
        <i class="icon-clock"></i>
        <span itemprop="datePublished"><?php the_date('Y年m月d日（D）'); ?></span>
        <small><?= hametuha_passed_time($post->post_date) ?></small>
        <meta itemprop="dateModified" content="<?= $post->post_modified ?>">
    </li>

    <!-- Comments -->
    <?php if( post_type_supports(get_post_type(), 'comments') ): ?>
        <li>
            <i class="icon-bubbles2"></i>
            <?php comments_number('なし', '1件','%件') ?>
        </li>
    <?php endif; ?>

    <!-- Edit link -->
    <?php if( current_user_can('edit_others_post', get_the_ID()) ): ?>
        <li>
            <i class="icon-pen3"></i> <?php edit_post_link() ?>
        </li>
    <?php endif; ?>
</ul><!-- //.metadata -->