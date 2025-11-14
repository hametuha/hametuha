<?php
/**
 * 破滅派の共通ヘッダー
 */
get_header( 'meta' );
?>
<header id="header" class="navbar navbar-expand-lg navbar-light fixed-top" role="navigation">
	<div class="container d-flex justify-content-between align-items-center">
		<!-- Toggle buttons (left side) -->
		<div class="d-flex justify-content-start">
			<button class="navbar-toggler d-inline-block" type="button" data-bs-toggle="offcanvas" data-bs-target="#header-navigation"
				aria-controls="header-navigation" aria-expanded="false" aria-label="Toggle navigation">
				<i class="icon-menu6"></i>
			</button>

			<?php if ( ! is_hamenew() ) : ?>
				<button class="navbar-toggler navbar-write write-panel-btn ms-2 d-inline-block" type="button">
					<i class="icon-pen4"></i>
				</button>
			<?php endif; ?>
		</div>

		<!-- Logo (center) -->
		<a class="navbar-brand logo" rel="home" href="<?php echo home_url( '/' ); ?>">
			<i class="icon-hametuha"></i><span><?php bloginfo( 'name' ); ?></span>
		</a>

		<!-- User info (right side) -->
		<div id="user-info"></div>

	</div><!-- .container -->
</header>

<!-- Offcanvas Navigation Menu -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="header-navigation" aria-labelledby="header-navigationLabel">
	<?php get_header( 'menu' ); ?>
</div>
<!-- // .offcanvas -->
