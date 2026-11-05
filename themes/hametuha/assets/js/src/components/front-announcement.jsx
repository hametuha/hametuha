/*!
 * トップページのお知らせを順に切り替える（#327）
 *
 * 最新のお知らせをしばらく見せてから、一定間隔でローテーションする。
 * マウスを乗せている間・フォーカス中は止め、「視差効果を減らす」設定では切り替えない。
 *
 * @handle hametuha-front-announcement
 */

/** 最初の1件（最新）を出しておく時間 */
const FIRST_DURATION = 10000;
/** 2件目以降の表示時間 */
const DURATION = 5000;

const init = ( nav ) => {
	const items = Array.from( nav.querySelectorAll( '.front-announcement__item' ) );
	const counter = nav.querySelector( '.front-announcement__counter' );
	const current = nav.querySelector( '.front-announcement__current' );
	if ( items.length < 2 ) {
		return;
	}
	if ( counter ) {
		counter.hidden = false;
	}
	if ( window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
		return;
	}

	let index = 0;
	let timer = null;
	let paused = false;

	const show = ( next ) => {
		items[ index ].classList.remove( 'is-active' );
		index = next;
		items[ index ].classList.add( 'is-active' );
		if ( current ) {
			current.textContent = String( index + 1 );
		}
	};

	const schedule = () => {
		clearTimeout( timer );
		if ( paused ) {
			return;
		}
		timer = setTimeout( () => {
			show( ( index + 1 ) % items.length );
			schedule();
		}, 0 === index ? FIRST_DURATION : DURATION );
	};

	const pause = () => {
		paused = true;
		clearTimeout( timer );
	};
	const resume = () => {
		paused = false;
		schedule();
	};

	nav.addEventListener( 'mouseenter', pause );
	nav.addEventListener( 'mouseleave', resume );
	nav.addEventListener( 'focusin', pause );
	nav.addEventListener( 'focusout', resume );

	schedule();
};

document.addEventListener( 'DOMContentLoaded', () => {
	document.querySelectorAll( '.front-announcement' ).forEach( init );
} );
