
<div class="author-profile" itemprop="author" itemscope itemtype="http://data-vocabulary.org/Person">
    <?= get_avatar(get_the_author_meta('ID'), 160, '', get_the_author()) ?>
    <h3>
        <span itemprop="name"><?php the_author() ?></span>
        <small>
            <span itemprop="affiliation">破滅派</span>
            <span itemprop="role"><?= hametuha_user_role(get_the_author_meta('ID')) ?></span>
        </small>
    </h3>
    <div class="author-desc">
        <?= wpautop(esc_html(get_the_author_meta('description'))) ?>
    </div>
    <ul class="list-inline">
        <li><i class="icon-calendar"></i> <?= hametuha_passed_time(get_the_author_meta('user_registered')) ?>登録</li>
        <li>
            <i class="icon-quill3"></i> <?= number_format(get_author_work_count()) ?>作品
            （<a href="<?= get_author_posts_url(get_the_author_meta('ID')) ?>" itemprop="url">一覧</a>）
        </li>
        <li>
            <i class="icon-globe"></i>
            <?php
                $url = get_the_author_meta('user_url');
                if( $url != 'http://' && !empty($url) ):
                    $site_name = get_the_author_meta('aim');
                    if( !$site_name ){
                        $site_name = trim_long_sentence($url, 14);
                    }
            ?>
                <a href="<?= esc_attr($url) ?>" itemprop="url"><?= esc_html($site_name) ?></a>
            <?php else: ?>
                サイトなし
            <?php endif; ?>
        </li>
    </ul>
</div>
