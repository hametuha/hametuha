/*!
 * 検索フォームのヘルパー
 *
 * @handle hametuha-components-post-search-helper
 */

( function( $ ) {
	'use strict';

	$( document ).ready( function() {
		const $form = $( '#post-filter-form' );
		if ( ! $form.length ) {
			return;
		}

		// ジャンルラジオボタンの変更を監視
		const $genreRadios = $form.find( 'input[name="genre"]' );
		$genreRadios.on( 'change', function() {
			const $selected = $( this );
			const newAction = $selected.data( 'action' );

			if ( newAction ) {
				$form.attr( 'action', newAction );
				console.log( 'Form action changed to:', newAction );
			}
		} );
	} );

}( jQuery ) );