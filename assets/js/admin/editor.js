/*!
 * Admin editor
 *
 * @handle hametuha-edit-form
 * @deps jquery
 */

const $ = jQuery;

$( document ).ready( function () {
	// タグ
	const $tagInput = $( '#hametuha-tag-input' );
	if ( $tagInput.length ) {
		const $inputs = $( '.hametuha-tag-cb' );
		const $extraInput = $( '.hametuha-tag-extra' );
		const updateTagValue = function () {
			const tags = [];
			// チェックボックスを取得
			$( '.hametuha-tag-cb:checked' ).each( function ( index, input ) {
				tags.push( $( input ).val() );
			} );
			// テキストエリアを取得
			$.each( $extraInput.val().replace( '、', ',' ).split( ',' ), function ( index, tag ) {
				const t = $.trim( tag );
				if ( t.length ) {
					tags.push( t );
				}
			} );
			$( '#hametuha-tag-input' ).val( tags.join( ', ' ) );
		};
		// チェックボックスを監視
		$inputs.change( function () {
			updateTagValue();
		} );
		// テキストエリアに使われていないタグを移植
		const extraTags = [];
		$.each( $tagInput.val().split( ', ' ), function ( index, tag ) {
			let found = false;
			$inputs.each( function ( i, t ) {
				if ( tag === $( t ).val() ) {
					found = true;
					return false;
				}
			} );
			if ( ! found ) {
				extraTags.push( tag );
			}
		} );
		$extraInput.val( extraTags.join( ', ' ) );
		$extraInput.keyup( updateTagValue );
	}

	// よくある質問タグ
	$( '.taxonomy-check-list' ).on( 'click', '.taxonomy-check-box', function () {
		const tags = [],
			$p = $( this ).parents( '.taxonomy-check-list' );
		$p.find( 'input:checked' ).each( function ( index, cb ) {
			tags.push( $( cb ).val() );
		} );
		$p.prev( 'input' ).val( tags.join( ', ' ) );
	} );
} );

