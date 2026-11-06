/*!
 * リスト作成用のフォーム
 *
 * @feature-group list
 * @handle hametuha-components-list-creator
 * @deps jquery, hametuha-common, wp-api-fetch, hametuha-toast
 */


const $ = jQuery;
const { apiFetch } = wp;
const { toast } = wp.hametuha;

// リスト作成用モーダル
$( document ).on( 'click', '.list-creator', function ( e ) {
	e.preventDefault();
	const post_id = $( this ).attr( 'data-post-id' ) || 'new';
	apiFetch( {
		path: `hametuha/v1/lists/${post_id}/`,
	} ).then( ( res ) => {
		Hametuha.modal.open( $( this ).attr( 'title' ), function ( box ) {
			box.removeClass( 'loading' );
			box.find( '.modal-body' ).html( res.html );
		} );
	} ).catch( ( res ) => {
		toast( res.message || '編集用のフォームを開けませんでした。', 'danger', 'エラー' );
	});
} );

// リスト作成フォーム
$( document ).on( 'submit', '.list-create-form', function ( e ) {
	e.preventDefault();
	const form = $( this );
	form.addClass( 'loading' );
	form.ajaxSubmit( {
		success: function ( result ) {
			if ( result.success ) {
				form.trigger( 'created.hametuha', [ result.post ] );
			} else {
				toast( result.message, 'danger', 'エラー' )
			}
			form.find( 'input[type=submit]' ).attr( 'disabled', false );
			form.removeClass( 'loading' );
		},
		error: function( result ) {
			toast( result.message || 'エラーが発生しました', 'danger', 'エラー' );
			form.removeClass( 'loading' );
		}
	} );
} );

// モーダルの中のリスト
$( '.modal' ).on( 'created.hametuha', '.list-create-form', function ( e, post ) {
	Hametuha.modal.close();
	Hametuha.ga.hitEvent( 'list', 'add', post.ID );
	if ( $( 'body' ).hasClass( 'single-lists' ) ) {
		location.reload();
	}
} );
