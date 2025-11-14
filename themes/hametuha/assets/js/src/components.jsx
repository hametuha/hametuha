/*!
 * Components
 *
 * @handle hametuha-components
 * @deps wp-element
 */

if ( ! window.wp.hametuha ) {
	window.wp.hametuha = {};
}

/**
 * オブジェクトのプロパティ名からクラス名を表示する
 *
 * @param {object} classes key is class name, value is boolean.
 * @returns {string} Class names.
 */
const classNames = ( classes )	=> {
	const attr = [];
	Object.keys( classes ).forEach( (key) => {
		if ( classes[ key ] ) {
			attr.push( key );
		}
	});
	return attr.join( ' ' );
};
window.wp.hametuha.classNames = classNames;


/**
 * Convert date object to string.
 *
 * @param {Date} date
 * @return {string}
 */
const toDateTime = ( date ) => {
	const str = [ date.getFullYear() ];
	for ( const s of [ date.getMonth() + 1, date.getDate() ] ) {
		str.push( ( '0' + s ).slice( -2 ) );
	}
	return str.join( '-' );
};
window.wp.hametuha.toDateTime = toDateTime;

/**
 * formの特定の要素をPOST送信用のオブジェクトに変換する
 *
 * WordPress REST APIに準拠した形式で変換：
 * - radio: 選択された値（文字列）
 * - checkbox（単一）: boolean
 * - checkbox（複数）: 選択された値の配列
 * - その他（text, textarea, select, number等）: value
 *
 * @param {HTMLElement} form form要素
 * @param {Array<string>} names nameの配列
 * @return {Object}
 */
const formToData = ( form, names ) => {
	const formData = {};
	names.forEach( ( name ) => {
		const elements = form.querySelectorAll( `[name="${name}"]` );
		if ( elements.length === 0 ) {
			return;
		}

		const firstElem = elements[0];
		const type = firstElem.type;

		if ( type === 'radio' ) {
			// ラジオボタン: チェックされた要素のvalue
			const checked = form.querySelector( `[name="${name}"]:checked` );
			formData[name] = checked ? checked.value : null;
		} else if ( type === 'checkbox' ) {
			if ( elements.length === 1 ) {
				// 単一チェックボックス: boolean
				formData[name] = firstElem.checked;
			} else {
				// 複数チェックボックス: チェックされた要素のvalueの配列
				formData[name] = Array.from( elements )
					.filter( elem => elem.checked )
					.map( elem => elem.value );
			}
		} else {
			// その他（text, textarea, select, number等）: valueをそのまま取得
			formData[name] = firstElem.value;
		}
	} );
	return formData;
};
window.wp.hametuha.formToData = formToData;

/**
 * Bootstrap 5 Tooltip初期化
 *
 * data-bs-toggle="tooltip"を持つすべての要素にtooltipを初期化
 * DOMの変更後に再度呼び出すことで動的に追加された要素にも対応可能
 */
const initializeTooltips = () => {
	const tooltipTriggerList = document.querySelectorAll( '[data-bs-toggle="tooltip"]' );
	const tooltipList = Array.from( tooltipTriggerList ).map( tooltipTriggerEl => {
		// Bootstrap 5ではbootstrap.Tooltipコンストラクタを使用
		return new bootstrap.Tooltip( tooltipTriggerEl );
	} );
	return tooltipList;
};

// ページロード時に初期化
document.addEventListener( 'DOMContentLoaded', () => {
	initializeTooltips();
} );

// 再初期化用の関数を公開
window.wp.hametuha.initializeTooltips = initializeTooltips;
