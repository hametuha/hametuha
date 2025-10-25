/*!
 * 検索フォームのヘルパー
 *
 * @handle hametuha-components-post-search-helper
 * @deps jquery
 */

const $ = jQuery;

$( document ).ready( function() {
	const $form = $( '#post-filter-form' );
	if ( ! $form.length ) {
		return;
	}

	// ジャンルラジオボタンの変更を監視
	const $genreRadios = $form.find( 'input[name="genre"]' );

	// 初期表示時にチェック済みのラジオボタンのdata-actionを設定
	const $checkedGenre = $genreRadios.filter( ':checked' );
	if ( $checkedGenre.length ) {
		const initialAction = $checkedGenre.data( 'action' );
		if ( initialAction ) {
			$form.attr( 'action', initialAction );
			console.log( 'Initial form action set to:', initialAction );
		}
	}

	// ラジオボタン変更時の処理
	$genreRadios.on( 'change', function() {
		const $selected = $( this );
		const newAction = $selected.data( 'action' );

		if ( newAction ) {
			$form.attr( 'action', newAction );
			console.log( 'Form action changed to:', newAction );
		}
	} );
} );
