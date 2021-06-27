/*!
 * 破滅派サイト全体で共通して読み込まれるファイル
 *
 * @handle hametuha-common
 * @deps jquery, bootbox
 */

/*global HametuhaGlobal:false */

window.Hametuha = {
	ga: {
		/**
		 * Google Analyticsのイベントを呼ぶ
		 *
		 * @param {string} category
		 * @param {string} action
		 * @param {string} label
		 * @param {...number} value Default 1
		 * @param {...boolean} nonInteraction Default false
		 */
		hitEvent: function ( category, action, label, value, nonInteraction ) {
			try {
				if ( 'undefined' === typeof value ) {
					value = 1;
				}
				ga( 'send', {
					hitType: 'event',
					eventCategory: category,
					eventAction: action,
					eventLabel: label,
					eventValue: value,
					nonInteraction: !!nonInteraction
				} );
			} catch ( err ) {
			}
		},

		/**
		 * URLの移動をGAに記録する
		 *
		 * @param {Event} event
		 * @param {string} url
		 * @param {string} category
		 * @param {string} action
		 * @param {string} label
		 * @param {...number} value
		 */
		eventOutbound: function ( event, url, category, action, label, value ) {
			try {
				if ( 'undefined' === typeof value ) {
					value = 1;
				}
				// Send event
				ga( 'send', {
					hitType: 'event',
					eventCategory: category,
					eventAction: action,
					eventLabel: label,
					eventValue: value,
					hitCallback: function () {
						if ( 'share' === category ) {
							window.open( url, 'outbound', "width=520, height=350" );
						} else {
							window.open( url, 'outbound' );
						}
					}
				} );
				// stopEvent
				event.preventDefault();
			} catch ( err ) {
			}
		}
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
		 * @returns {*|boolean}
		 */
		startYakumono: function ( string ) {
			return this.yakumono.test( string );
		}
	},

	/**
	 * 投稿が縦書きかどうか
	 *
	 * @returns {boolean}
	 */
	isTategaki: function () {
		return $( 'body' ).hasClass( 'tategaki' );
	},

	/**
	 * Angularのtemplateを返す
	 *
	 * @param templateName
	 * @returns {*}
	 */
	template: function ( templateName ) {
		return HametuhaGlobal.angularTemplateDir + templateName;
	},

	/**
	 * モデルを格納する名前空間
	 *
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
		 * @param {string} title
		 * @param {string|Function} body
		 */
		open: function ( title, body ) {
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
		close: function () {
			const $box = $( '#hametu-modal' );
			this.reset();
			$box.modal( 'hide' );
		},

		reset: function () {
			const $box = $( '#hametu-modal' );
			$box.find( '.modal-title' ).html( '' );
			$box.find( '.modal-body' ).html( '' );
		}

	}
};
