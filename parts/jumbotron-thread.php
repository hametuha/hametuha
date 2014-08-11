<?php

$url = is_post_type_archive('thread') ? '' : home_url('/thread/', 'http');

?>
<div class="jumbotron" id="thread-tron">
    <img src="<?= get_template_directory_uri() ?>/assets/img/jumbotron/thread.jpg" alt="コンスタンティヌス公会議が行われた場所">
    <h1>破滅派掲示板</h1>
    <p><?= esc_html(get_post_type_object('thread')->description) ?></p>
    <p><a id="add-thread" class="btn btn-success btn-lg" href="<?= $url ?>#thread-add">スレッドを立てる</a></p>
</div>