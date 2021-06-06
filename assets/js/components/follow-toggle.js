/*!
 * Follow toggle
 *
 * @handle hametu-follow
 * @deps twitter-bootstrap, wp-api
 */

const $ = jQuery;

// フォロー・アンフォロー
$( document ).on( 'click', 'a.btn-follow', function ( e ) {
	const endpoint = wpApiSettings.root + 'hametuha/v1/doujin/follow/' + $( this ).attr( 'data-follower-id' ) + '/';
	const $btn = $( this );
	e.preventDefault();
	if ( $btn.hasClass( 'btn-follow--loading' ) ) {
		return;
	}
	const following = $( this ).hasClass( 'btn-following' );
	if ( following ) {
		Hametuha.confirm( 'フォローを解除してよろしいですか？', function () {
			$btn.addClass( 'btn-follow--loading' );
			$.ajax( {
				url: endpoint,
				method: 'DELETE',
				beforeSend: function ( xhr ) {
					xhr.setRequestHeader( 'X-WP-Nonce', wpApiSettings.nonce );
				},
				data: {}
			} ).done( function () {
				$btn.removeClass( 'btn-following' );
			} ).fail( function () {
				Hametuha.alert( 'フォローを解除できませんでした', true );
			} ).always( function () {
				$btn.removeClass( 'btn-follow--loading' );
			} );
		}, true );
	} else {
		$btn.addClass( 'btn-follow--loading' );
		$.ajax( {
			url: endpoint,
			method: 'POST',
			beforeSend: function ( xhr ) {
				xhr.setRequestHeader( 'X-WP-Nonce', wpApiSettings.nonce );
			},
			data: {}
		} ).done( function ( response ) {
			$btn.addClass( 'btn-following' );
		} ).fail( function () {
			Hametuha.alert( 'フォローに失敗しました。すでにフォロー済みか、サーバが混み合っています。', true );
		} ).always( function () {
			$btn.removeClass( 'btn-follow--loading' );
		} );
	}

} );
