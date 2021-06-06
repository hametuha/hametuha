/*!
 * Watermark handler.
 *
 * @package hametuha
 * @handle hametuha-watermark
 * @deps jquery
 */

const $ = jQuery;

$( document ).ready( function () {
	const $watermark = $( '#watermark' );
	if ( $watermark.length ) {
		$watermark.click( function () {
			$( this ).toggleClass( 'toggle' );
		} );
	}
} );
