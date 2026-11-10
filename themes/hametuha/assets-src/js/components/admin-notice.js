/*!
 * ログイン中ユーザーへの緊急告知（uploads/notification.json をポーリング）
 *
 * @feature-group admin-notice
 * @handle hametuha-admin-notice
 * @deps hametuha-toast
 */

const config = window.HametuhaAdminNotice || {};
const INTERVAL = ( config.interval || 120 ) * 1000;
const STORAGE_KEY = 'hametuha-admin-notice-dismissed';

/**
 * 既読かどうか
 *
 * @param {string} id
 * @return {boolean} 既読ならtrue
 */
const isDismissed = ( id ) => {
	try {
		return window.localStorage.getItem( STORAGE_KEY ) === id;
	} catch ( e ) {
		return false;
	}
};

/**
 * 既読にする
 *
 * @param {string} id
 */
const dismiss = ( id ) => {
	try {
		window.localStorage.setItem( STORAGE_KEY, id );
	} catch ( e ) {
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
	// 120秒単位で固定したタイムスタンプ。同じ窓の中ではCloudflareのキャッシュが効く。
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
		if ( isDismissed( id ) ) {
			return;
		}
		if ( data.until && Date.parse( data.until ) < Date.now() ) {
			return; // 消し忘れ対策
		}
		dismiss( id );
		// 閉じるまで表示し続ける（約1日）
		window.wp.hametuha.toast(
			String( data.message ),
			'warning',
			data.title || '',
			86400000
		);
	} catch ( e ) {
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
