/**
 * Description
 */

/*global HametuhaUserSelect: true*/

( function ( $ ) {
	'use strict';

	const select2 = function ( select ) {
		$( select ).select2( {
			language: 'ja',
			placeholder: '検索してください',
			maximumSelectionLength: 1,
			minimumInputLength: 2,
			templateResult( user ) {
				return $(
					'<span class="user-selector-item"><img alt="" src="' +
						user.avatar +
						'" />' +
						user.text +
						'</span>'
				);
			},
			ajax: {
				url:
					HametuhaUserSelect.endpoint +
					$( select ).attr( 'data-mode' ),
				dataType: 'json',
				delay: 300,
				data( params ) {
					return {
						s: params.term,
						mode: $( this ).attr( 'data-mode' ),
						_wpnonce: HametuhaUserSelect.nonce,
					};
				},
				processResults( data, params ) {
					const items = [];
					for ( let i = 0, l = data.length; i < l; i++ ) {
						items.push( {
							id: data[ i ].ID,
							text: data[ i ].name + '（' + data[ i ].role + '）',
							avatar: data[ i ].avatar,
						} );
					}
					return {
						results: items,
						pagination: {
							more: false,
						},
					};
				},
				escapeMarkup( markup ) {
					return markup;
				},
			},
		} );
	};

	$( document ).on( 'initialized.userSelect', '.select', function () {
		select2( this );
	} );

	$( document ).ready( function () {
		$( 'select[data-module="user-select"]' ).each(
			function ( index, select ) {
				select2( select );
			}
		);
	} );
} )( jQuery );
