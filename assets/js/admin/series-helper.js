/*!
 * Series edit helper
 *
 * @handle hametuha-admin-series-helper
 * @deps jquery-ui-sortable,backbone,udnderscore,jquery-effects-highlight
 */

const $ = jQuery;


// 本文プレビュー
$( '#epub-previewer' ).change( function ( e ) {
	const val = $( this ).val(),
		$form = $( '<form target="epub-preview">' +
			'<input type="hidden" name="direction" />' +
			'<input type="hidden" name="post_id" />' +
			'</form>' );
	if ( val.length ) {
		$form.attr( 'action', $( this ).attr( 'data-endpoint' ) );
		$form.find( 'input[name=direction]' ).val( $( 'input[name=orientation]:checked' ).val() == 'vertical' ? 'rtl' : 'ltr' );
		$form.find( 'input[name=post_id]' ).val( val );
		$( 'body' ).append( $form );
		$form.submit();
		$form.remove();
	}
} );

// その他のプレビュー
$( '#series-additons-list' ).on( 'click', 'a', function ( e ) {
	const url = $( this ).attr( 'href' ) + '?direction=' + ( 'vertical' === $( 'input[name="orientation"]:checked' ).val() ? 'rtl' : 'ltr' );
	e.preventDefault();
	window.open( url, $( this ).attr( 'target' ) );
} );

$( document ).ready( function () {
	// 並び順
	const $sorter = $( '#series-posts-list' );
	$sorter.sortable( {
		axis: 'y',
		handle: '.dashicons-menu',
		opacity: 0.8,
		placeholder: "sortable-placeholder",
		containment: "parent",
		update: function () {
			const $lis = $( this ).find( 'li' ),
				start = $lis.length;
			$lis.each( function ( index, li ) {
				$( li ).find( 'input[name^=series_order]' ).val( start - index );
			} );
		}
	} ).on( 'click', '.button--delete', function ( e ) {
		e.preventDefault();
		if ( window.confirm( 'この作品を作品集から除外しますか？' ) ) {
			const $li = $( this ).parents( 'li' );
			$.post( $sorter.attr( 'data-endpoint' ), {
				action: 'series_list',
				_seriesordernonce: $sorter.attr( 'data-nonce' ),
				series: $sorter.attr( 'data-post-id' ),
				post_id: $( this ).attr( 'data-id' )
			} ).done( function ( result ) {
				if ( result.success ) {
					$li.remove();
				} else {
					Hametuha.alert( result.message, true );
				}
			} ).fail( function () {
				// Do nothing
			} ).always( function () {
				// Do nothing
			} );
		}
	} );
	// 希望小売価格
	$( '#change-price' ).change( function () {
		if ( $( this ).prop( 'checked' ) ) {
			$( '#change-price-box' ).removeClass( 'hidden' );
		} else {
			$( '#change-price-box' ).addClass( 'hidden' );
		}
	} );

} );
