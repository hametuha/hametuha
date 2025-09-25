/*!
 * アイデアをお勧めする機能
 *
 * @handle hametuha-components-ideas-recommend
 * @deps jquery, hametuha-toast, wp-api-fetch, hametuha-components-user-picker
 */
alert( 'hogehoge' );
const $ = jQuery;
const { apiFetch } = wp;
const { toast } = wp.hametuha;

// ストックボタン
$( document ).on( 'submit', '#idea-recommender', function ( e ) {
	e.preventDefault();
	const $button = $( this ).find( 'input[type="submit"]' );
	const post_id = $( this ).attr( 'data-post-id' );
	const recommend_to = $( this ).find( '#recommend_to' ).val();
	if ( ! recommend_to || ! /\d+/.test( recommend_to ) ) {
		// ユーザーIDが指定されていないので何もしない
		console.log( '推薦', recommend_to, post_id );
		return;
	}
	$button.attr( 'disabled', true ).text( '送信中…' );
	const originalText = $button.text();
	// リクエスト実行
	apiFetch( {
		path: `hametuha/v1/idea/${post_id}/?user_id=${recommend_to}`,
		method: 'PUT',
	} ).then( ( response ) => {
		toast( response.message );
		// todo: フォームを閉じる
	} ).catch( ( response ) => {
		const message = response.message || '失敗しました。';
		toast( message, 'danger', 'エラー' );
	} ).finally( () => {
		$button.attr( 'disabled', null ).text( originalText );
	} );
} );
