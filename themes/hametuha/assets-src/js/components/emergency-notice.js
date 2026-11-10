/*!
 * ログイン中ユーザーへの緊急告知（uploads/notification.json をポーリング）
 *
 * 平常時はファイルが無く 404 になる。nginx でこのパスを PHP に渡さないこと（#452）。
 *
 * @feature-group emergency-notice
 * @handle hametuha-emergency-notice
 * @deps hametuha-toast
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
		// 閉じるまで表示し続ける（約1日）。閉じたときだけ既読にするので、
		// 読まずにページを移動した人には次のページでもう一度出る。
		const toast = window.wp.hametuha.toast(
			String( data.message ),
			'warning',
			data.title ? String( data.title ) : '',
			86400000
		);
		toast
			.querySelector( '.hametuha-toast-close' )
			.addEventListener( 'click', () => dismiss( id ) );
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
