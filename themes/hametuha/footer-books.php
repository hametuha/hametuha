<?php
/**
 * 書籍のコンテナ
 *
 * @var array $args
 */
$args = wp_parse_args( $args, [
	'color' => '',
] );
?>
<section class="book-section-full books-section-inverse books-full" style="<?php echo $args['color'] ? esc_html( sprintf( 'background-color: %s;', $args['color'] ) ) : ''; ?> ?">
	<div class="container">
		<?php
		get_sidebar( 'books', [
			'title'       => true,
			'description' => true,
		] );
		?>
	</div>
</section>
