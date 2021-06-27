<?php
/**
 * Render header menu.
 *
 * @package hametuha
 */

// Enqueue script.
wp_enqueue_script( 'hametuheader' );
// Add SVG.
wp_localize_script( 'hametuheader', 'HametuHeaderVars', [
	'svg' => get_template_directory_uri() . '/dist/img/bi/bootstrap-icons.svg',
] );
get_header( 'meta' );
?>
<header id="header" class="site-navigation" role="navigation">
	<div class="container">

		<button class="header-toggle no-appearance">
			<?php echo hametuha_embed_bi_svg( 'list', 32 ); ?>
		</button>

		<?php if ( is_hamenew() ) : ?>
			<a class="logo" rel="home" href="<?php echo home_url( '/news' ); ?>">
				<img class="logo-img logo-img-hamenew" loading="eager" src="<?php echo get_template_directory_uri(); ?>/dist/img/hamenew-logo.png"
					alt="<?php esc_attr_e( 'はめにゅー', 'hametuha' ); ?>" width="180" height="100" />
			</a>
		<?php else : ?>
			<a class="logo" rel="home" href="<?php echo home_url( '/' ); ?>">
				<img class="logo-img logo-img-hametuha" src="<?php echo get_template_directory_uri(); ?>/dist/img/brand/hametuha.svg"
					width="380" height="147" loading="eager" alt="<?php esc_attr_e( '破滅派', 'hametuha' ); ?>" />
			</a>
		<?php endif; ?>

		<div id="user-info" class="user-login-wrapper"></div>

	</div><!-- .container -->
</header>

