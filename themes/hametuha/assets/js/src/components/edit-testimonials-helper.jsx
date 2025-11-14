/*!
 * 推薦コメントを管理する
 *
 * @feature-group series
 * @deps jquery, wp-api-fetch, wp-i18n, hametuha-toast
 * @handle hametuha-components-edit-testimonials-helper
 */

const $ = jQuery;
const { apiFetch } = wp;
const { __ } = wp.i18n;
const { toast, formToData } = wp.hametuha;

// 削除ボタンを押したらREST APIを叩く
$( document ).on( 'click', '.testimonial-delete', function( e ) {
	e.preventDefault();
	const $button = $( this );
	if ( $button.prop( 'disabled' ) ) {
		// すでに削除中なら何もしない
		return;
	}
	$button.prop( 'disabled', true ).text( __( '削除中…', 'hametuha' ) );
	apiFetch( {
		path: $button.attr( 'data-path' ),
		method: 'delete'
	} ).then( ( res ) => {
		toast( res.message, 'success', __( '削除完了', 'hametuha' ) );
		$button.parents( '.testimonialList__item' ).remove();
	} ).catch( ( res ) => {
		toast( res.message, 'danger', __( 'エラー', 'hametuha' ) );
		$button.prop( 'disabled', null ).text( __( '削除', 'hametuha' ) );
	} );
} );

// レビューの編集（編集専用）
$( document ).on( 'submit', '.testimonial-edit-form', function ( e ) {
	e.preventDefault();
	const $form = $( this );
	const $button = $form.find( 'input[type=submit]' );
	const commentId = $form.attr( 'data-id' );

	// 編集フィールド（基本 + 編集専用）
	const fields = [ 'source', 'url', 'rank', 'text', 'display', 'priority', 'excerpt' ];

	const data = formToData( this, fields.map( ( name ) => {
		return `testimonial-${name}`;
	} ) );

	// 送信中の処理
	$button.prop( 'disabled', true );
	const originalText = $button.val();
	$button.val( __( '送信中…', 'hametuha' ) );

	apiFetch( {
		path: `hametuha/v1/testimonials/${commentId}/`,
		method: 'put',
		data,
	} ).then( ( res ) => {
		toast( res.message, 'success', __( '成功', 'hametuha' ) );
		// 編集成功後はページをリロード
		setTimeout( () => {
			window.location.reload();
		}, 1500 );
	} ).catch( ( res ) => {
		toast( res.message, 'danger', __( 'エラー', 'hametuha' ) );
		$button.prop( 'disabled', false ).val( originalText );
	} );
} );
