<?php

add_filter( 'body_class', function ( $classes ) {
	$classes[] = 'quote-style-' . ( ( isset( $_GET['style'] ) ) ? (int) $_GET['style'] : rand( 1, 3 ) );
	return $classes;
} );

/** @var WP_Post $post */
/** @var string  $text */
/** @var WP_User $user */
get_header( 'meta' );
setup_postdata( $post );
?>


<div class="quote-container">

	<table class="quote-body">
		<tr>
			<td class="quote-body-cell">
				<?php
				$separated = $this->tokenize( $text );
				if ( is_wp_error( $separated ) ) {
					echo esc_html( $text );
				} else {
					echo implode( '', array_map( function ( $text ) {
						return sprintf( '<span class="quote-token">%s</span>', esc_html( $text ) );
					}, $separated ) );
				}
				?>
			</td>
		</tr>
	</table>

	<div class="quote-meta">

		<div class="quote-title">
			<strong>『<?php the_title(); ?>』</strong>
			<?php the_author(); ?>
		</div>

		<div class="quote-via">
			Quoted by <?php echo esc_html( $user->display_name ); ?>
		</div>

	</div>

</div>
<script>
	jQuery(document).ready(function($){
		var $body = $('.quote-body');
		var footer = $('.quote-meta').height();
		var size = Math.ceil( ( 1100 - footer ) / Math.sqrt( $body.text().length ) / 1.8 );
		$body.css('font-size', size + 'px');
	});
</script>
<?php wp_footer(); ?>
</body>
</html>
