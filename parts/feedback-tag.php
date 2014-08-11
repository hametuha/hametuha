<?php
/** @var Hametuha\QueryHighJack\UserTag $this */
$tags = $this->user_tag->get_post_tags(get_the_ID(), get_current_user_id());
?>

<h3 class="text-center">タグ</h3>
<?php if( is_user_logged_in() ): ?>
<p class="text-muted">
    <span class="text-danger">赤いタグ</span>はあなたがつけたものです。白いタグは他のユーザーが追加したものです。
</p>
<?php endif; ?>
<div class="all-tags tag-container<?php if( empty($tags) ) echo ' no-tag'?>" id="user-tag-list" data-post-id="<?php the_ID() ?>">
    <?php foreach( $tags as $tag ): ?>
        <a class="<?php if($tag->owning) echo 'me' ?>" href="<?= get_tag_link($tag) ?>" data-taxonomy-id="<?= $tag->term_taxonomy_id ?>" data-term="<?= esc_attr($tag->name) ?>" data-number="<?= $tag->number ?>">
            <?= esc_html($tag->name) ?> (<?= $tag->number > 100 ? '100+' : $tag->number ?>)
            <?php if( is_user_logged_in() ): ?>
            <i></i>
            <?php endif; ?>
        </a>
    <?php endforeach; ?>
</div><!-- // all-tags -->
<p class="alert alert-info">
    この投稿にはまだ誰もタグをつけていません。ぜひ最初のタグをつけてください！
</p>
<div class="your-tags">
    <h4 class="container-subtitle"><i class="icon-tags2"></i> タグをつける</h4>
    <?php if( is_user_logged_in() ): ?>
        <form id="user-tag-editor" method="post" action="<?= home_url('/user-tags/add/'.get_the_ID()) ?>/">
            <div class="input-group">
                <input type="text" class="form-control" maxlength="17" placeholder="17文字以内でタグ付け">
                <span class="icon-spinner11 form-control-feedback rotation"></span>
                <span class="input-group-btn">
                    <input type="submit" class="btn btn-success" value="送信" />
                </span>
            </div>
        </form>
    <?php else: ?>
        <p class="alert alert-warning">
            タグ付け機能は会員限定です。<a class="alert-link" href="<?php echo wp_login_url(get_permalink()); ?>">ログインまたは新規登録</a>をしてください。
        </p>
    <?php endif; ?>
</div><!-- //.your-tags -->

<div class="author-tags">
    <h4 class="container-subtitle"><i class="icon-graduation"></i> 作者がつけたタグ<?php help_tip('作者が自らつけたタグです。古い作品は編集部がつけた場合があります。');?></h4>
    <p class="tag-container">
        <?php if( get_the_tags() ): ?>
            <?php the_tags('', ' '); ?>
        <?php else: ?>
            ---
        <?php endif; ?>
    </p>
</div>