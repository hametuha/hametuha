/*!
 * User selector
 *
 * @handle hametuha-user-select
 * @deps select2
 */

/*global HametuhaUserSelect: true*/

const $ = jQuery;

const select2 = function ( select ) {
	$( select ).select2( {
		language: 'ja',
		placeholder: '検索してください',
		maximumSelectionLength: 1,
		minimumInputLength: 2,
		templateResult: function ( user ) {
			return $( '<span class="user-selector-item"><img alt="" src="' + user.avatar + '" />' + user.text + '</span>' );
		},
		ajax: {
			url: HametuhaUserSelect.endpoint + $( select ).attr( 'data-mode' ),
			dataType: 'json',
			delay: 300,
			data: function ( params ) {
				return {
					s: params.term,
					mode: $( this ).attr( 'data-mode' ),
					_wpnonce: HametuhaUserSelect.nonce
				};
			},
			processResults: function ( data, params ) {
				const items = [];
				for ( let i = 0, l = data.length; i < l; i++ ) {
					items.push( {
						id: data[ i ].ID,
						text: data[ i ].name + '（' + data[ i ].role + '）',
						avatar: data[ i ].avatar
					} );
				}
				return {
					results: items,
					pagination: {
						more: false
					}
				};
			},
			escapeMarkup: function ( markup ) {
				return markup;
			}
		}
	} );
};

$( document ).on( 'initialized.userSelect', '.select', function () {
	select2( this );
} );

$( document ).ready( function () {
	$( 'select[data-module="user-select"]' ).each( function ( index, select ) {
		select2( select );
	} );
} );
