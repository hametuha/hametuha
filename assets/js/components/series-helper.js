/*!
 * Series page
 *
 * @handle hametuha-series
 * @deps jquery-masonry, jquery-form
 */

const $ = jQuery;


$( document ).ready( function () {

	// レビュー追加
	$( '.review-creator' ).on( 'click', function ( e ) {
		e.preventDefault();
		const url = $( this ).attr( 'href' );
		const title = $( this ).attr( 'data-title' );
		Hametuha.modal.open( title, function ( box ) {
			const $body = box.find( '.modal-body' );
			$body.empty();
			$.get( url ).done( function ( result ) {
				$body.html( result );
			} ).fail( function () {
				$body.html( '<div class="alert alert-danger">レビューを追加できません。</div>' );
			} ).always( function () {
				box.removeClass( 'loading' );
			} );
		} );
	} );

	// レビュー送信
	$( document ).on( 'submit', '#testimonial-form', function ( e ) {
		e.preventDefault();
		const $form = $( this );
		const $button = $form.find( 'input[type=submit]' );
		const errorHandler = function ( msg ) {
			const $error = $( '<div class="alert alert-danger">' + msg + '</div>' );
			$form.before( $error );
			$button.attr( 'disabled', false );
			setTimeout( function () {
				$error.remove();
			}, 3000 );
		};
		$button.attr( 'disabled', true );
		$form.ajaxSubmit( {
			success: function ( result ) {
				if ( result.success ) {
					$form.after( '<div class="alert alert-success">' + result.message + '</div>' );
					$form.remove();
					setTimeout( Hametuha.modal.close, 1500 );
				} else {
					errorHandler( result.message );
				}
			},
			error: function () {
				errorHandler( '送信に失敗しました。' );
			}
		} );
	} );

} );
