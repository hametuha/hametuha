<div class="jumbotron" id="thread-tron" style="background-image: url('<?= get_template_directory_uri() ?>/assets/img/jumbotron/ideas.jpg');" title="Auguste Rodin 'Le Panseur' Photo by wilburn White">
	<h1>破滅派アイデア帳</h1>
	<p><?= esc_html(get_post_type_object('ideas')->description) ?></p>
	<p>
		<?php if ( is_user_logged_in() ) : ?>
			<a class="btn btn-success btn-lg" href="<?= home_url( '/my/ideas/new/' ) ?>" data-action="post-idea">アイデアを投稿</a>
		<?php else : ?>
			<a class="btn btn-success btn-lg" href="<?= wp_login_url( $_SERVER['REQUEST_URI'] ); ?>">アイデアを投稿</a>
		<?php endif; ?>
	</p>
</div>
