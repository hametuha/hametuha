<?php
/** @var WP_Post $post */
/** @var string  $text */
/** @var WP_User $user */
get_header( 'meta' );
setup_postdata( $post );
?>


<div class="quote-container" data-style="<?= ( isset( $_GET['style'] ) ) ? 'style-' . (int) $_GET['style'] : '' ?>">

	<div class="quote-body">
		<?php
		$separated = $this->tokenize( $text );
		if ( is_wp_error( $separated ) ) {
			echo esc_html( $text );
		} else {
			echo implode('', array_map( function( $text ) {
				return sprintf( '<span class="quote-token">%s</span>', esc_html( $text ) );
			}, $separated ) );
		}
		?>
	</div>

	<div class="quote-meta">

		<div class="quote-title">
			<strong>『<?php the_title() ?>』</strong>
			<?php the_author() ?>
		</div>

		<div class="quote-via">
			Quoted by <?= esc_html( $user->display_name ) ?>
		</div>

	</div>

</div>
<script>
	jQuery(document).ready(function($){
	  var $body = $('.quote-body');
	  var footer = $('.quote-meta').height();
	  var size = Math.ceil( ( 1100 - footer ) / Math.sqrt( $body.text().length ) / 1.8 );
	  $body.css('font-size', size + 'px');

	  var height = $body.height();
	  $body.css('margin-top', '-' + ( height / 2 + footer ) + 'px' );
	  // Change style
	  var $container = $('.quote-container');
	  var container_class = $container.attr('data-style');
	  if ( container_class ) {
	    $('body').addClass( container_class);
	  } else {
		$('body').addClass('style-' + ( Math.round( ( Math.random() * 3)) + 1) );
	  }
	});
</script>
<?php wp_footer(); ?>
</body>
</html>
