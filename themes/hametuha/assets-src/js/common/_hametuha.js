/*
 * 破滅派サイト全体で共通して読み込まれるファイル
 */

/*global HametuhaGlobal:false */

( function ( $ ) {
	'use strict';

	const Hametuha = ( window.Hametuha = {
		ga: {
			/**
			 * Google Analyticsのイベントを呼ぶ（GA4対応）
			 *
			 * @param {string}    category
			 * @param {string}    action
			 * @param {string}    label
			 * @param {...number} value    Default 1
			 */
			hitEvent( category, action, label, value ) {
				try {
					if ( 'undefined' === typeof value ) {
						value = 1;
					}
					gtag( 'event', action, {
						event_category: category,
						event_label: label,
						value,
					} );
				} catch ( err ) {}
			},

			/**
			 * URLの移動をGAに記録する（GA4対応）
			 *
			 * @param {Event}     event
			 * @param {string}    url
			 * @param {string}    category
			 * @param {string}    action
			 * @param {string}    label
			 * @param {...number} value
			 */
			eventOutbound( event, url, category, action, label, value ) {
				try {
					if ( 'undefined' === typeof value ) {
						value = 1;
					}
					// Send event
					gtag( 'event', action, {
						event_category: category,
						event_label: label,
						value,
						event_callback() {
							window.location.href = url;
						},
					} );
					// stopEvent
					event.preventDefault();
				} catch ( err ) {}
			},
		},

		str: {
			/**
			 * 約物開始判定用正規表現
			 *
			 * @type {RegExp}
			 */
			yakumono: /^[ 　【】《〔〝『「（”"'’\(\)]/,

			/**
			 * 文字列が約物で始まるかどうか
			 *
			 * @param {string} string
			 * @return {*|boolean}
			 */
			startYakumono( string ) {
				return this.yakumono.test( string );
			},
		},

		/**
		 * グローバルメッセージを表示する
		 *
		 * @param {string} message
		 * @param {string} [type]
		 * @param {number} [delay]
		 */
		alert( message, type, delay ) {
			let typeName, body, $alert;
			if ( undefined === delay ) {
				delay = 7000;
			}
			switch ( type ) {
				case 'info':
				case 'danger':
				case 'warning':
					typeName = type;
					break;
				case true: // Backward compats
				case 'error':
					typeName = 'danger';
					break;
				default:
					typeName = 'success';
					break;
			}
			body =
				'<div class="alert alert-' +
				typeName +
				' alert-dismissible alert-sticky" role="alert"><div class="container">' +
				'<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
				message +
				'</div></div>';
			$alert = $( body );
			if ( $( '#whole-body' ).length ) {
				$( '#whole-body' ).append( $alert );
			} else {
				$( 'body' ).append( $alert );
			}
			setTimeout( function () {
				$alert.addClass( 'alert-sticky-on' );
			}, 10 );
			setTimeout( function () {
				$alert.removeClass( 'alert-sticky-on' );
				setTimeout( function () {
					$alert.remove();
				}, 300 );
			}, delay );
		},

		/**
		 * Show confirm dialog
		 *
		 * @param {string}   message
		 * @param {Function} [callback]
		 * @param {boolean}  [deletable]
		 */
		confirm( message, callback, deletable ) {
			bootbox.dialog( {
				title: '確認',
				message,
				buttons: {
					cancel: {
						label: 'キャンセル',
						className: 'btn-default',
					},
					ok: {
						label: deletable ? '実行' : 'OK',
						className: deletable ? 'btn-danger' : 'btn-success',
						callback,
					},
				},
			} );
		},

		/**
		 * 投稿が縦書きかどうか
		 *
		 * @return {boolean}
		 */
		isTategaki() {
			return $( 'body' ).hasClass( 'tategaki' );
		},

		/**
		 * モデルを格納する名前空間
		 * @type {Object}
		 */
		models: {},

		/**
		 * ビューを格納する名前空間
		 */
		views: {},

		/**
		 * コレクションを格納する名前空間
		 */
		collections: {},

		/**
		 * モーダル関係
		 */
		modal: {
			/**
			 * モーダルボックスを表示する
			 *
			 * @param {string}            title
			 * @param {string | Function} body
			 */
			open( title, body ) {
				this.reset();
				const $box = $( '#hametu-modal' );
				$box.find( '.modal-title' ).html( title );
				if ( typeof body === 'function' ) {
					//
					$box.addClass( 'loading' );
					body( $box );
				} else {
					// 追加して開く
					$box.find( '.modal-body' ).html( body );
				}
				$box.modal( 'show' );
			},
			/**
			 * モーダルボックスを閉じる
			 */
			close() {
				const $box = $( '#hametu-modal' );
				this.reset();
				$box.modal( 'hide' );
			},

			reset() {
				const $box = $( '#hametu-modal' );
				$box.find( '.modal-title' ).html( '' );
				$box.find( '.modal-body' ).html( '' );
			},
		},
	} );
} )( jQuery );
