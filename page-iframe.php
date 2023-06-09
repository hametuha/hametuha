<?php
/*
 * Template Name: irame専用テンプレート
 */
if ( ! isset( $_GET['iframe'] ) || ! $_GET['iframe'] ) {
	wp_die( 'このページを直接開くことはできません' );
}
get_header( 'meta' ); ?>
<body <?php body_class( 'iframe' ); ?>>
<?php
if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		?>
	<h1 class="sans"><?php the_title(); ?></h1>
	<div class="post-content">
			<?php the_content(); ?>
	</div>
		<?php
	endwhile;
endif;
?>
<?php wp_footer(); ?>
</body>
</html>
