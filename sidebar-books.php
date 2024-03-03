<?php
/**
 * Book list template.
 *
 * @var array $args
 */

$books = hametuha_get_minicome_product();
if ( ! $books ) {
	return;
}

$args = wp_parse_args( $args, [
	'title' => false,
] );

?>
<section class="books-wrapper" style="margin-top: 40px; margin-bottom: 80px;">

	<?php if ( $args['title'] ) : ?>
	<h2 class="page-header text-center" style="margin-bottom: 20px; border-bottom: none;">
		<small>Books</small><br />
		<?php esc_html_e( '破滅派の書籍', 'hametuha' ); ?>
	</h2>
	<?php endif; ?>

	<div class="books-list">

		<?php
		$counter = 0;
		foreach ( $books as $book ) :
			if ( 6 <= $counter ) {
				break;
			}
			$counter++;
			$img = $book['media']['woocommerce_single'];
			?>

			<div class="books-item">
				<a class="books-link" href="<?php echo esc_attr( $book['url'] ); ?>" target="_blank" rel="noopener noreferrer">
					<img loading="lazy" class="books-cover" src="<?php echo esc_url( $img['url'] ); ?>" alt="<?php echo esc_attr( $book['title'] ); ?>" width="<?php echo esc_attr( $img['width'] ); ?>" height="<?php echo esc_attr( $img['height'] ); ?>" />
				</a>
			</div>

		<?php endforeach; ?>

	</div>
</section>
