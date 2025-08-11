<?php
/** @var \Hametuha\Rest\EPub $this */
/** @var WP_Post $series */

$publisher = '破滅派';
if ( hametuha_is_secret_book() ) {
	$publisher = $this->series->override_meta( get_the_author_meta( 'ID' ), '_publisher_name', $publisher );
}
?>
<?php get_template_part( 'templates/epub/header' ); ?>

<section class="titlepage" epub:type="titlepage">

	<!-- 表紙なし -->
	<h1 class="titlepage__title">
		<span><?php the_title(); ?></span>
		<?php if ( ( $subtitle = get_post_meta( get_the_ID(), 'subtitle', true ) ) ) : ?>
			<small class="subtitle"><?php echo esc_html( $subtitle ); ?></small>
		<?php endif; ?>
		<small><?php echo esc_html( hametuha_author_name() ); ?></small>
		<small class="publisher-credit">
			<?php echo esc_html( $publisher ); ?>
		</small>
	</h1>

</section>

<?php get_template_part( 'templates/epub/footer' ); ?>
