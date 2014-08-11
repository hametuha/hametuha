<?php
/** @var \Hametuha\Ajax\Feedback $this */

?>
<h3 class="text-center">あなたの反応</h3>

<p class="star-rating<?php if( is_user_logged_in() ) echo ' active' ?>">
    <?php for( $i = 1; $i <= 5; $i++ ):?>
        <i data-value="<?= $i ?>" class="icon-star6<?php if( $i <= $your_rating ) echo ' active' ?>"></i>
    <?php endfor;?>
    <input type="hidden" name="rating" value="<?= $your_rating ?>" />
</p>

<?php if( !is_user_logged_in() ): ?>
    <p class="alert alert-warning"><a href="<?= wp_login_url(get_permalink()) ?>" class="alert-link">ログイン</a>すると、星の数によって冷酷な評価を突きつけることができます。</p>
<?php endif; ?>


<input type="hidden" name="post_id" value="<?php the_ID() ?>" />

<?php foreach( $reviews as $key => $review ): ?>
    <div class="review-labels">
        <h4><?= esc_html($review_label[$key]) ?></h4>
        <div class="btn-group btn-group-justified" data-toggle="buttons">
            <?php foreach( $review as $index => list($label, $value, $checked) ): ?>
                <label class="btn btn-xs btn-default<?php if($checked) echo ' active' ?>">
                    <input type="radio" class="btn btn-xs btn-info" name="<?= $key ?>" value="<?= $value ?>" <?php checked($checked) ?> />
                    <?= esc_html($label) ?>
                </label>
            <?php endforeach; ?>
        </div>
    </div>
<?php endforeach; ?>
<input type="submit" value="送信" data-complete-text="評価済み" data-loading-text="送信中..." class="btn btn-info btn-block" />
