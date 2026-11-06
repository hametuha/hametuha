/*!
 * リスト詳細画面
 *
 * @feature-group list
 * @handle hametuha-components-list
 * @deps hametuha-components-list-creator
 */

const $ = jQuery;
const { apiFetch } = wp;
const { toast } = wp.hametuha;

// リスト削除ボタン
$( document ).on( 'click', '.list-eraser', function ( e ) {
	e.preventDefault();
	const postId = $( this ).attr( 'data-post-id' );
	if ( ! postId ) {
		return;
	}
	Hametuha.confirm( $( this ).attr( 'title' ), function () {
		apiFetch( {
			path: `hametuha/v1/lists/${postId}`,
			method: 'delete'
		} ).then( ( res ) => {
			toast( res.message, 'success', '削除成功' );
			if ( $( 'body' ).hasClass( 'single-lists' ) ) {
				setTimeout( () => {
					// シングルページの場合はリスト一覧に移動
					window.location.href = result.url;
				}, 1000 );
			}
		} ).catch( ( res ) => {
			toast( res.message, 'danger', 'エラー' );
		} );
		$.post( $( this ).attr( 'href' ), function ( result ) {
		} );
	}, true );
} );

// リストのシングルページで投稿を削除する
$( document ).ready( function ( $ ) {

	// リストから投稿を削除
	const listTpl = $( '#my-list-deleter' );
	if ( listTpl.length ) {
		const listId = listTpl.data( 'list-id' );
		// ボタンを追加
		$( 'ol.media-list > li' ).each( function ( index, elt ) {
			$( elt ).find( '.list-inline' ).append(
				'<li class="list-inline-item"><button " class="deregister-button btn btn-sm btn-outline-danger">&times; リストから削除</button></li>'
			);
		} );
		// イベントリスナー
		$( 'ol.media-list' ).on( 'click', '.deregister-button', function ( e ) {
			e.preventDefault();
			const btn = $( this );
			const li = $( this ).parents( 'li[data-post-id]' );
			const postId = li.data( 'post-id' );
			Hametuha.confirm( 'リストからこの作品を削除します。この操作は取り消せませんが、よろしいですか？', function () {
				apiFetch( {
					method: 'put',
					path: `hametuha/v1/lists/${listId}/?post_id=${postId}&action=remove`,
				} ).then( ( res ) => {
					li.remove();
					if ( ! $( 'ol.media-list > li' ).length ) {
						$( 'ol.media-list' ).before( '<div class="alert alert-danger">' + result.message + '</div>' );
						setTimeout( function () {
							window.location.href = res.home_url;
						}, 3000 );
					} else {
						toast( res.message );
					}
				} ).catch( ( res ) => {
					toast( res.message, 'danger', 'エラー' );
				} );
			}, true );
		} );
	}
} );
