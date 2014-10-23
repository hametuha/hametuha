<div class="jumbotron" id="thread-tron">
    <img src="<?= get_template_directory_uri() ?>/assets/img/jumbotron/lists.jpg" alt="Pieter Brueghel the Elder (1526/1530–1569) [Public domain or Public domain], via Wikimedia Commons">
    <h1>みんなで作る作品リスト</h1>
    <p><?= esc_html(get_post_type_object('lists')->description) ?></p>
    <p><a class="btn btn-success btn-lg" href="#about-list">もっと詳しく</a></p>
</div>