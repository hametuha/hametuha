<?php get_header( 'meta' ); ?>
<header id="header" class="navbar navbar-default navbar-fixed-top" role="navigation">
	<div class="container">

		<span class="navbar-status">
			<?= esc_html( $title ) ?>
		</span>

		<div class="navbar-header">
			<a href="<?= esc_url( $return ) ?>" class="navbar-brand" title="編集をやめる" id="quit-editting">
				<span class="sr-only">編集をやめる</span>
				<span class="icon-esc"></span>
			</a>
		</div>


		<?php get_template_part( 'templates/header/user' ) ?>


	</div>
	<!-- .navbar -->

</header>
