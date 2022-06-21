<?php
/**
 * Book list template.
 */

$books = hametuha_get_minicome_product();
if ( ! $books ) {
	return;
}

?>
<section class="books-wrapper">

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
