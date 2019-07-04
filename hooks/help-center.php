<?php
/**
 * Help center related hooks.
 *
 * @package hametuha
 */


/**
 * Facebookチャットを表示する
 */
add_action( 'wp_footer', function() {
	static $did = false;
	if ( $did ) {
		return;
	}
	if ( is_singular( 'faq' ) || is_post_type_archive( 'faq' ) || is_tax( 'faq_cat' ) || is_page( 'help' ) ) {
		?>
		<!-- Your customer chat code -->
		<div class="fb-customerchat"
			 attribution=setup_tool
			 page_id="196103120449777"
			 theme_color="#000000"
			 logged_in_greeting="めつかれさまです。なにかお困りですか？"
			 logged_out_greeting="めつかれさまです。なにかお困りですか？">
		</div>
		<?php
	}
} );
