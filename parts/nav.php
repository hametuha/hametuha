<ul class="nav nav-pills">
    <li class="<?= is_home() ? 'active' : '' ?>"><a href="<?= get_post_type_archive_link('thread', 'http'); ?>">すべての作品</a></li>
    <?php foreach( get_categories(['hide_empty' => false, 'parent' => 0]) as $term ){
        printf('<li class="%s"><a href="%s">%s</a></li>', is_category($term->slug) ? 'active' : '', get_category_link($term), esc_html($term->name));
    } ?>
</ul>
