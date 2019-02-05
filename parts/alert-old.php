<?php if ( $diff = hametuha_date_diff_formatted( 365 ) && in_array( get_post_type(), [ 'post', 'faq' ] ) ) : ?>
    <div class="alert alert-warning">
        この<?= esc_html( get_post_type_object( get_post_type() )->label ) ?>が公開されたのは
        <strong><?= $diff ?>以上前</strong>です。
        場合によっては内容が無効になっている可能性がありますので、その点ご了承ください。
    </div>
<?php endif; ?>