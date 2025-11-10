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
	'title'       => false,
	'description' => false,
] );

?>
<section class="books-wrapper" style="margin-top: 40px; margin-bottom: 80px;">

	<?php if ( $args['title'] ) : ?>
	<h2 class="page-header text-center" style="margin-bottom: 20px; border-bottom: none;">
		<small>Books</small><br />
		<?php esc_html_e( '破滅派の書籍', 'hametuha' ); ?>
	</h2>
	<?php endif; ?>

	<?php if ( $args['description'] ) : ?>
		<div class="d-flex justify-content-center mt-3 mb-3">
			<p class="text-muted" style="max-width: 480px;">
				<?php echo nl2br( esc_html__( "破滅派は同人サークルから出発していまや出版社となりました。\n破滅派の書籍は書店・通販サイトでお求めいただけます。", 'hametuha' ) ); ?>
			</p>
		</div>
	<?php endif; ?>

	<div class="books-list">

		<?php
		$counter = 0;
		foreach ( $books as $book ) :
			if ( 12 <= $counter ) {
				break;
			}
			$counter++;
			$img = $book['media']['woocommerce_single'];
			$title = $book['title'];
			if ( ! empty( $book['price'] ) && is_numeric( $book['price'] ) ) {
				$title .= sprintf( '（税抜%s円）', number_format( $book['price'] ) );
			}
			?>
			<div class="books-item">
				<a class="books-link" href="<?php echo esc_attr( $book['url'] ); ?>" target="_blank" rel="noopener noreferrer">
					<img loading="lazy" class="books-cover"
						src="<?php echo esc_url( $img['url'] ); ?>"
						alt="<?php echo esc_attr( $book['title'] ); ?>" title="<?php echo esc_attr( $title ); ?>"
						width="<?php echo esc_attr( $img['width'] ); ?>" height="<?php echo esc_attr( $img['height'] ); ?>" />
				</a>
			</div>

		<?php endforeach; ?>

	</div>
	<p class="text-center mt-5">
		<a class="btn btn-primary" href="https://minico.me">
			<?php esc_html_e( '破滅派の通販サイトを見る', 'hametuha' ); ?>
		</a>
	</p>
</section>
