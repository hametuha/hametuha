<?php

$image = get_template_directory_uri() . '/assets/img/jumbotron/lists.jpg';

?>
<div class="jumbotron" id="thread-tron" style="background-image: url('<?php echo $image; ?>');" title="Pieter Brueghel the Elder (1526/1530–1569) [Public domain or Public domain], via Wikimedia Commons">
	<h1>みんなで作る作品リスト</h1>
	<p><?php echo esc_html( get_post_type_object( 'lists' )->description ); ?></p>
	<p><a class="btn btn-success btn-lg" href="#about-list">もっと詳しく</a></p>
</div>
