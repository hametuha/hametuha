/*!
 * 連載のコントロール
 *
 * @handle hametuha-series
 * @deps jquery, wp-api-fetch, hametuha-toast, wp-i18n
 */

const $ = jQuery;
const { apiFetch } = wp;
const { __ } = wp.i18n;
const { toast, formToData } = wp.hametuha;

// レビュー追加
$( document ).ready( function () {
	$( '.review-creator' ).on( 'click', function ( e ) {
		e.preventDefault();
		var url = $( this ).attr( 'href' ),
			title = $( this ).attr( 'data-title' );
		Hametuha.modal.open( title, function ( box ) {
			var $body = box.find( '.modal-body' );
			$body.empty();
			$.get( url ).done( function ( result ) {
				$body.html( result );
			} ).fail( function () {
				toast( __( 'レビューを追加できません', 'hametuha' ), 'danger', __( 'エラー', 'hametuha' ) );
				Hametuha.modal.close();
			} ).always( function () {
				box.removeClass( 'loading' );
			} );
		} );
	} );

} );

// レビュー送信（新規作成のみ）
$( document ).on( 'submit', '#testimonial-form', function ( e ) {
	e.preventDefault();
	const $form = $( this );
	const $button = $form.find( 'input[type=submit]' );
	const postId = $form.attr( 'data-id' );

	const data = formToData( this, [ 'source', 'url', 'rank', 'text' ].map( ( name ) => {
		return `testimonial-${name}`;
	} ) );

	// 送信中の処理
	$button.prop( 'disabled', true );
	const originalText = $button.val();
	$button.val( __( '送信中…', 'hametuha' ) );

	apiFetch( {
		path: `hametuha/v1/testimonials/${postId}/`,
		method: 'post',
		data,
	} ).then( ( res ) => {
		toast( res.message, 'success', __( '成功', 'hametuha' ) );
	} ).catch( ( res ) => {
		toast( res.message, 'danger', __( 'エラー', 'hametuha' ) );
		$button.prop( 'disabled', false ).val( originalText );
	} ).finally( () => {
		Hametuha.modal.close();
	} );
} );

