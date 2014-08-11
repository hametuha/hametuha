<?php
    $url = is_user_logged_in() ? admin_url('post-new.php?post_type=anpi') : '';
?>

<div class="jumbotron" id="thread-tron">
    <img src="<?= get_template_directory_uri() ?>/assets/img/jumbotron/anpi.jpg" alt="Pieter Brueghel the Elder (1526/1530–1569) [Public domain or Public domain], via Wikimedia Commons">
    <h1>破滅派 安否情報</h1>
    <p><?= esc_html(get_post_type_object('anpi')->description) ?></p>
    <p><a class="btn btn-success btn-lg" href="<?= $url ?>">安否情報を書く</a></p>
</div>