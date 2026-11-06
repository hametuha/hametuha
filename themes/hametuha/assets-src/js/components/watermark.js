/**
 * Watermark handler.
 *
 * @package
 */

jQuery( document ).ready( function ( $ ) {
	'use strict';

	const $watermark = $( '#watermark' );
	if ( $watermark.length ) {
		$watermark.click( function ( e ) {
			$( this ).toggleClass( 'toggle' );
		} );
	}
} );
