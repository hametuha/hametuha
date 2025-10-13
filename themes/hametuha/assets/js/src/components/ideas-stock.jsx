/*!
 * アイデアをストックしたり、進めたりする機能
 *
 * @feature-group idea
 * @handle hametuha-components-ideas-stock
 * @deps jquery, hametuha-toast, wp-api-fetch
 */

const $ = jQuery;
const { apiFetch } = wp;
const { toast } = wp.hametuha;

// ストックボタン
$( document ).on( 'click', 'button[data-stock]', function ( e ) {
	const $button = $( this );
	const post_id = $button.attr( 'data-stock' );
	e.preventDefault();
	const method = $button.attr( 'data-stock-action' ).toUpperCase();
	let newAction, newLabel, toastMessage;
	switch ( method ) {
		case 'DELETE':
			// 実行前に確認
			if ( ! window.confirm( 'ストックを解除します。よろしいですか？' ) ) {
				return;
			}
			newAction = 'post';
			newLabel = 'ストックする';
			toastMessage = 'ストックを解除しました。';
			break;
		case 'POST':
			newAction = 'delete';
			newLabel = 'ストック済み';
			toastMessage = 'このアイデアをストックしました。';
			break;
		default:
			return; // 該当するアクションなし
	}
	$button.attr( 'disabled', true );
	// リクエスト実行
	apiFetch( {
		path: `hametuha/v1/idea/${post_id}/`,
		method: method,
	} ).then( ( response ) => {
		$button.attr( 'data-stock-action', newAction )
			.attr( 'data-unstock', post_id ).text( newLabel );
		toast( toastMessage );
	} ).catch( ( response ) => {
		const message = response.message || '失敗しました。';
		toast( message, 'danger', 'エラー' );
	} ).finally( () => {
		$button.attr( 'disabled', null );
	} );
} );
