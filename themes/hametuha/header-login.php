<?php
add_filter( 'body_class', function ( $classes ) {
	$classes[] = 'no-header';
	return $classes;
} );

get_header( 'meta' );
?>
<div id="login-form" class="container">

	<h1 class="text-center">
		<a href="<?php echo home_url( '/' ); ?>">
			<i class="icon-hametuha"></i><span class="hidden"><?php bloginfo( 'name' ); ?></span>
		</a>
	</h1>
