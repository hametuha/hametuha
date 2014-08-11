<?php
$url = is_user_logged_in() ? admin_url('post-new.php?post_type=anpi') : '';
?>

<div class="jumbotron" id="help-tron">
    <img src="<?= get_template_directory_uri() ?>/assets/img/jumbotron/faq.jpg" alt="apanese Painting Anthlogy, ed.et publ. by SINBI-SHOIN, TOKYO, 1941">
    <h1>よくある質問</h1>
    <p><?= esc_html(get_post_type_object('faq')->description) ?></p>
</div>