<form method="get" action="<?= home_url('/', 'http') ?>" class="adv-search-form">
	<?php if( is_post_type_archive('faq') || is_tag('cat_faq') || is_singular('faq') ): ?>
    	<input type="hidden" name="post_type" value="faq" />
    <?php elseif( is_post_type_archive('thread') || is_tax('topic') || is_singular('thread') ): ?>
        <input type="hidden" name="post_type" value="thread" />
	<?php else: ?>
        <div class="input-group">
            <label for="form-post-type">検索対象</label>
            <select name="post_type" id="form-post-type" class="form-control">
                <?php
                    $current_post_type = get_query_var('post_type');
                    $post_types = ['post', 'faq', 'thread', 'announcement', 'anpi'];
                ?>

                <option value="any"<?php selected(!$current_post_type || 'any' == $current_post_type) ?>>すべて</option>
                <?php foreach( $post_types as $post_type ): $obj = get_post_type_object($post_type); ?>
                <option value="<?= $post_type ?>"<?php selected($post_type == $current_post_type) ?>><?= $obj->label ?></option>
                <?php endforeach; ?>
            </select>
        </div>
	<?php endif; ?>

    <div class="input-group">
        <input placeholder="検索ワードを入れてください" type="text" name="s" class="form-control" value="<?php the_search_query(); ?>">
        <span class="input-group-btn">
            <input type="submit" class="btn btn-default" value="検索">
        </span>
    </div><!-- /input-group -->
</form>