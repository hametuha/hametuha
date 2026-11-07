/*!
 * カルーセル（Swiper）
 *
 * カルーセルを出すテンプレート（parts/carousel-controls.php）が読み込む。全ページ共通の common.js には入れない
 * （Swiper は gzip で約17KB あるため）。
 *
 * マークアップは .carousel[data-carousel] の中に .swiper > .swiper-wrapper > .swiper-slide と、
 * parts/carousel-controls.php（前後ボタン・ドット）を置く。
 *
 * - books: 書籍のフッター（sidebar-books.php）
 * - kdp-widget: 電子書籍ウィジェット（Hametuha\Widget\KdpWidget）
 *
 * @feature-group carousel
 * @handle hametuha-carousel
 * @strategy defer
 */

import Swiper from 'swiper';
import { A11y, Autoplay, Navigation, Pagination } from 'swiper/modules';

/**
 * 種類ごとの設定。breakpoints は「その幅以上」で効く
 *
 * @type {Object<string, Object>}
 */
const settings = {
	books: {
		slidesPerView: 2,
		slidesPerGroup: 1,
		breakpoints: {
			481: {
				slidesPerView: 3,
				slidesPerGroup: 2,
			},
			770: {
				slidesPerView: 4,
				slidesPerGroup: 1,
			},
		},
	},
	'kdp-widget': {
		slidesPerView: 1,
		slidesPerGroup: 1,
	},
};

const reduceMotion = window.matchMedia(
	'(prefers-reduced-motion: reduce)'
).matches;

/**
 * カルーセルを初期化する
 */
const init = () => {
	document
		.querySelectorAll( '.carousel[data-carousel]' )
		.forEach( initCarousel );
};

/**
 * 1つのカルーセルを初期化する
 *
 * @param {HTMLElement} root .carousel 要素
 */
const initCarousel = ( root ) => {
	const setting = settings[ root.dataset.carousel ];
	const container = root.querySelector( '.swiper' );
	if ( ! setting || ! container ) {
		return;
	}
	new Swiper( container, {
		modules: [ A11y, Autoplay, Navigation, Pagination ],
		loop: true,
		speed: 200,
		autoplay: reduceMotion
			? false
			: {
					delay: 2000,
					disableOnInteraction: false,
					pauseOnMouseEnter: true,
				},
		navigation: {
			prevEl: root.querySelector( '.carousel__prev' ),
			nextEl: root.querySelector( '.carousel__next' ),
		},
		pagination: {
			el: root.querySelector( '.carousel__dots' ),
			clickable: true,
		},
		a11y: {
			prevSlideMessage: '前へ',
			nextSlideMessage: '次へ',
			paginationBulletMessage: '{{index}} 番目へ',
		},
		...setting,
	} );
};

if ( 'loading' === document.readyState ) {
	document.addEventListener( 'DOMContentLoaded', init );
} else {
	init();
}
