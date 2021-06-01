<?php
$image = get_template_directory_uri() . '/assets/img/jumbotron/anpi.jpg';
?>

<div class="jumbotron" id="thread-tron" style="background-image: url('<?php echo $image; ?>');"
	 title="Pieter Brueghel the Elder (1526/1530–1569) [Public domain or Public domain], via Wikimedia Commons">
	<h1>破滅派 安否情報</h1>
	<p><?php echo esc_html( get_post_type_object( 'anpi' )->description ); ?></p>
	<p>
		<?php if ( current_user_can( 'read' ) ) : ?>
			<a class="btn btn-success btn-lg anpi-new" href="#">安否安否報告する</a>
		<?php else : ?>
			<a class="btn btn-success btn-lg" href="<?php echo wp_login_url( get_post_type_archive_link( 'anpi' ) ); ?>" rel="nofollow">ログインして安否報告</a>
		<?php endif; ?>

	</p>
</div>
