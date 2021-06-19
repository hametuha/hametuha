<?php get_header( 'meta' ); ?>
<header id="header" class="navbar navbar-default navbar-fixed-top" role="navigation">
	<div class="container">

		<div class="navbar-header">
			<a class="navbar-toggle" href="#header-navigation">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</a>

			<?php if ( ! is_hamenew() ) : ?>
			<button class="navbar-toggle navbar-write write-panel-btn">
				<i class="icon-quill"></i>
			</button>
			<?php endif; ?>

		</div>

		<?php if ( is_hamenew() ) : ?>
			<a class="logo" rel="home" href="<?php echo home_url( '/news' ); ?>">
				<img src="<?php echo get_template_directory_uri(); ?>/assets/img/hamenew-logo.png" alt="はめにゅー" width="90" height="50" />
			</a>
		<?php else : ?>
			<a class="logo" rel="home" href="<?php echo home_url( '/' ); ?>">
				<i class="icon-hametuha"></i><span>破滅派</span>
			</a>
		<?php endif; ?>

		<div id="user-info"></div>

	</div><!-- .container -->
</header>

