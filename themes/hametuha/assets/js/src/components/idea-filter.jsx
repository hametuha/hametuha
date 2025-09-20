/*!
 * アイデアの絞り込み
 *
 * @handle hametuha-components-idea-filter
 * @deps bootstrap
 */

document.addEventListener( 'DOMContentLoaded', () => {
	const form = document.getElementById( 'idea-filter-form' );
	if ( ! form ) {
		return;
	}

	// フォーム送信をインターセプト
	form.addEventListener( 'submit', ( e ) => {
		e.preventDefault();

		// 1. 種別に応じてベースURLを決定
		const selectedType = form.querySelector( 'input[name="idea_type"]:checked' );
		const baseUrl = selectedType ? selectedType.dataset.url : '/ideas/';

		// 2. クエリパラメータを構築
		const params = new URLSearchParams();

		// 検索キーワード（空でなければ追加）
		const searchInput = form.querySelector( 'input[name="s"]' );
		if ( searchInput && searchInput.value.trim() ) {
			params.append( 's', searchInput.value.trim() );
		}

		// タグ（チェックされたものをカンマ区切りで）
		const checkedTags = form.querySelectorAll( 'input[type="checkbox"][name="tag"]:checked' );
		if ( checkedTags.length > 0 ) {
			const tagValues = Array.from( checkedTags ).map( checkbox => checkbox.value );
			params.append( 'tag', tagValues.join( ',' ) );
		}

		// 3. URLを構築して遷移
		let finalUrl = baseUrl;
		const queryString = params.toString();
		if ( queryString ) {
			finalUrl += '?' + queryString;
		}

		window.location.href = finalUrl;
	} );

	// タグのチェックボックスが変更されたときにhidden inputを更新
	const tagCheckboxes = form.querySelectorAll( 'input[type="checkbox"][name="tag"]' );
	const hiddenTagInput = form.querySelector( 'input[type="hidden"][name="tag"]' );

	if ( hiddenTagInput && tagCheckboxes.length > 0 ) {
		// 初期状態：hidden inputの値からチェックボックスを設定
		const initialTags = hiddenTagInput.value ? hiddenTagInput.value.split( ',' ) : [];
		tagCheckboxes.forEach( checkbox => {
			if ( initialTags.includes( checkbox.value ) ) {
				checkbox.checked = true;
			}
		} );

		// チェックボックス変更時の処理
		tagCheckboxes.forEach( checkbox => {
			checkbox.addEventListener( 'change', () => {
				const checkedValues = Array.from( tagCheckboxes )
					.filter( cb => cb.checked )
					.map( cb => cb.value );
				hiddenTagInput.value = checkedValues.join( ',' );
			} );
		} );
	}

	// ラジオボタン変更時にフォームのactionを更新（フォールバック用）
	const typeRadios = form.querySelectorAll( 'input[name="idea_type"]' );
	typeRadios.forEach( radio => {
		radio.addEventListener( 'change', () => {
			if ( radio.checked && radio.dataset.url ) {
				form.action = radio.dataset.url;
			}
		} );
	} );
} );
