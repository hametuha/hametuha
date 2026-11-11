/*!
 * ログイン中ユーザーへの緊急告知（uploads/notification.json をポーリング）
 *
 * 平常時はファイルが無く 404 になる。nginx でこのパスを PHP に渡さないこと（#452）。
 *
 * 出し方は画面で変える。
 * - フロント・hashboard: トースト（hametuha-toast。PHP 側でフロントだけ読み込む）
 * - ブロックエディター: エディター上部の通知（core/notices）
 * - それ以外の管理画面（原稿のクラシックエディターを含む）: 見出しの下の .notice
 *
 * @feature-group emergency-notice
 * @handle hametuha-emergency-notice
 */

const config = window.HametuhaEmergencyNotice || {};
const INTERVAL = ( config.interval || 120 ) * 1000;
const STORAGE_KEY = 'hametuha-emergency-notice-dismissed';

/**
 * このページで表示中の通知 ID。ポーリングのたびに同じトーストを重ねないため。
 *
 * @type {string|null}
 */
let shownId = null;

/**
 * 閉じた（既読にした）かどうか
 *
 * @param {string} id
 * @return {boolean} 閉じていれば true
 */
const isDismissed = ( id ) => {
	try {
		return window.localStorage.getItem( STORAGE_KEY ) === id;
	} catch {
		return false;
	}
};

/**
 * 閉じたことを記録する
 *
 * @param {string} id
 */
const dismiss = ( id ) => {
	try {
		window.localStorage.setItem( STORAGE_KEY, id );
	} catch {
		// localStorage が使えなくても動作は継続する
	}
};

/**
 * ブロックエディターの画面か
 *
 * @return {boolean} core/editor のストアがあれば true
 */
const isBlockEditor = () => {
	const data = window.wp && window.wp.data;
	return !! ( data && data.select( 'core/editor' ) );
};

/**
 * ブロックエディターの上部に出す
 *
 * @param {string}     title
 * @param {string}     message
 * @param {() => void} onDismiss
 */
const showInBlockEditor = ( title, message, onDismiss ) => {
	window.wp.data
		.dispatch( 'core/notices' )
		.createNotice(
			'warning',
			title ? `${ title }：${ message }` : message,
			{
				id: 'hametuha-emergency-notice',
				isDismissible: true,
				onDismiss,
			}
		);
};

/**
 * 管理画面の見出しの下に .notice を出す
 *
 * @param {string}     title
 * @param {string}     message
 * @param {() => void} onDismiss
 */
const showInAdmin = ( title, message, onDismiss ) => {
	const notice = document.createElement( 'div' );
	notice.className =
		'notice notice-warning is-dismissible hametuha-emergency-notice';
	const p = document.createElement( 'p' );
	p.style.whiteSpace = 'pre-line';
	if ( title ) {
		const strong = document.createElement( 'strong' );
		strong.textContent = title;
		p.appendChild( strong );
		p.appendChild( document.createTextNode( '\n' ) );
	}
	p.appendChild( document.createTextNode( message ) );
	notice.appendChild( p );
	const button = document.createElement( 'button' );
	button.type = 'button';
	button.className = 'notice-dismiss';
	const label = document.createElement( 'span' );
	label.className = 'screen-reader-text';
	label.textContent = '閉じる';
	button.appendChild( label );
	button.addEventListener( 'click', () => {
		notice.remove();
		onDismiss();
	} );
	notice.appendChild( button );
	// WordPress が通知を並べる位置（.wp-header-end）。無ければ見出しの後ろ、最後は本文の先頭
	const anchor =
		document.querySelector( '.wp-header-end' ) ||
		document.querySelector(
			'#wpbody-content .wrap > h1, #wpbody-content .wrap > h2'
		);
	if ( anchor ) {
		anchor.after( notice );
	} else {
		const body =
			document.getElementById( 'wpbody-content' ) || document.body;
		body.prepend( notice );
	}
};

/**
 * 画面に合わせて表示する
 *
 * 閉じるまで表示し続ける。閉じたときだけ既読にするので、
 * 読まずにページを移動した人には次のページでもう一度出る。
 *
 * @param {string} id
 * @param {string} title
 * @param {string} message
 */
const show = ( id, title, message ) => {
	const onDismiss = () => dismiss( id );
	if ( document.body.classList.contains( 'wp-admin' ) ) {
		if ( isBlockEditor() ) {
			showInBlockEditor( title, message, onDismiss );
		} else {
			showInAdmin( title, message, onDismiss );
		}
		return;
	}
	// 約1日（実質、閉じるまで）
	const toast = window.wp.hametuha.toast(
		message,
		'warning',
		title,
		86400000
	);
	toast
		.querySelector( '.hametuha-toast-close' )
		.addEventListener( 'click', onDismiss );
};

/**
 * 通知を取得して表示する
 */
const check = async () => {
	if ( ! config.url ) {
		return;
	}
	// 間隔ごとに固定したタイムスタンプ。同じ窓の中では同じ URL になり、キャッシュが効く。
	const bucket = Math.floor( Date.now() / INTERVAL );
	try {
		const res = await fetch( config.url + '?t=' + bucket );
		if ( ! res.ok ) {
			return; // 404 = 通知不要
		}
		const data = await res.json();
		if ( ! data || ! data.message ) {
			return;
		}
		const id = String( data.id || data.message );
		if ( id === shownId || isDismissed( id ) ) {
			return;
		}
		if ( data.until && Date.parse( data.until ) < Date.now() ) {
			return; // 消し忘れ対策
		}
		shownId = id;
		show(
			id,
			data.title ? String( data.title ) : '',
			String( data.message )
		);
	} catch {
		// 通知はベストエフォート。失敗は無視する
	}
};

let timer = null;

const start = () => {
	check();
	timer = setInterval( check, INTERVAL );
};

document.addEventListener( 'visibilitychange', () => {
	if ( document.hidden ) {
		clearInterval( timer );
		timer = null;
	} else if ( ! timer ) {
		start();
	}
} );

if ( ! document.hidden ) {
	start();
}
