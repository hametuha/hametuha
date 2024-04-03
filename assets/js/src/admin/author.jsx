/*!
 * 管理画面で投稿者用に利用される関数
 *
 * @handle hametuha-author-selector
 * @deps select2
 */

jQuery( document ).ready( function( $ ) {
	const $selector = $( '#post_author_override' );
	if ( $selector.length ) {
		// Make this pulldown to select2.
		$selector.select2();
	}
} );
