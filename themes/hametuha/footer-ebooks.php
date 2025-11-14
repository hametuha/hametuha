<?php
/**
 * 書籍のコンテナ
 *
 * @var array $args
 */
$args   = wp_parse_args( $args, [
	'color' => 'var(--bs-gray-200)',
] );
$styles = [];
if ( $args['color'] ) {
	$styles = array_merge( $styles, [
		'background-color: ' . $args['color'],
		'padding: 40px 0',
	] );
}
?>
<section class="ebook-container" style="<?php echo esc_attr( implode( ';', $styles ) ); ?>">
	<div class="container">
		<h2 class="page-header text-center mb-3" style="border-bottom: none;">
			<small>Published eBooks</small>
			<br />
			<?php esc_html_e( '電子書籍', 'hametuha' ); ?>
		</h2>
		<?php get_template_part( 'templates/recommendations' ); ?>
	</div>
</section>
