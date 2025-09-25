/*!
 * アイデアをお勧めする機能
 *
 * @handle hametuha-components-ideas-recommend
 * @deps jquery, hametuha-toast, wp-api-fetch, hametuha-components-user-picker
 */
const $ = jQuery;
const { apiFetch } = wp;
const { toast } = wp.hametuha;

/**
 * フォームを閉じる処理
 */
const collapse = () => {
	// Bootstrap 5のモーダルを閉じる
	const modalElement = document.getElementById( 'ideaRecommendForm');
	if ( modalElement ) {
		modalElement.classList.remove( 'show' );
	}
	// フォームをリセット
	$( '#idea-recommender' )[0]?.reset();
	$( '#recommend_to' ).val( '' ).trigger( 'change' );
};

// 推薦フォームの送信
$( document ).on( 'submit', '#idea-recommender', function ( e ) {
	e.preventDefault();
	const $button = $( this ).find( 'input[type="submit"]' );
	const post_id = $( this ).attr( 'data-post-id' );
	const recommend_to = $( this ).find( '#recommend_to' ).val();
	if ( ! recommend_to || ! /\d+/.test( recommend_to ) ) {
		// ユーザーIDが指定されていないので何もしない
		toast( 'ユーザーを選択してください。', 'warning', '入力エラー' );
		return;
	}
	$button.attr( 'disabled', true );
	const originalText = $button.val();
	$button.val( '送信中…' );
	// リクエスト実行
	apiFetch( {
		path: `hametuha/v1/idea/${post_id}/?user_id=${recommend_to}`,
		method: 'PUT',
	} ).then( ( response ) => {
		toast( response.message );
		// フォームを閉じる
		collapse();
	} ).catch( ( response ) => {
		const message = response.message || '失敗しました。';
		toast( message, 'danger', 'エラー' );
	} ).finally( () => {
		$button.attr( 'disabled', null ).val( originalText );
	} );
} );

// キャンセルボタンのクリック処理
$( document ).on( 'click', '[data-dismiss="modal"]', function ( e ) {
	e.preventDefault();
	collapse();
} );
