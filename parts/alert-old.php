<?php
/**
 * Display alert if post is too old.
 *
 * @package hametuha
 */
if ( ! in_array( get_post_type(), [ 'announcement', 'faq' ] ) || ! hametuha_remarkably_old( 365 ) ) {
    return;
}
?>
<div class="alert alert-warning">
    この<?= esc_html( get_post_type_object( get_post_type() )->label ) ?>が公開されたのは
    <strong><?= hametuha_date_diff_formatted( 365 ) ?>以上前</strong>です。
    場合によっては内容が無効になっている可能性がありますので、その点ご了承ください。
</div>
