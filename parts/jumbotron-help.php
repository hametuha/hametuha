<?php
	$url = home_url('/inquiry', 'https');
	$image = get_template_directory_uri().'/assets/img/jumbotron/faq.jpg';
?>

<div class="jumbotron" id="help-tron" style="background-image: url('<?= $image ?>');" title="apanese Painting Anthlogy, ed.et publ. by SINBI-SHOIN, TOKYO, 1941">
    <h1>よくある質問</h1>
    <p><?= esc_html(get_post_type_object('faq')->description) ?></p>
	<p><a class="btn btn-success btn-lg" href="<?= $url ?>">お問い合わせ</a></p>
</div>