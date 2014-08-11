<form method="get" action="<?= home_url('/authors/', 'http') ?>">
    <div class="input-group">
        <input type="text" placeholder="著者名、読みがななどで絞り込み" name="s" class="form-control" value="<?php the_search_query(); ?>">
        <span class="input-group-btn">
            <input type="submit" class="btn btn-default" value="検索">
        </span>
    </div><!-- /input-group -->
</form>