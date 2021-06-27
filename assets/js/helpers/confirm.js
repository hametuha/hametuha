/*!
 * Hametuha Alert.
 *
 * @handle hametuha-confirm
 * @deps bootbox, wp-i18n
 */

/* global bootbox: false */

if ( ! window.Hametuha ) {
	window.Hametuha = {};
}
const { Hametuha } = window;
const { __ } = wp.i18n;

/**
 * Show confirm dialog
 *
 * @param {string} message
 * @param {Function} [callback]
 * @param {boolean} [deletable]
 */
Hametuha.confirm = function ( message, callback, deletable ) {
	bootbox.dialog( {
		title: __( '確認', 'hametuha' ),
		message: message,
		buttons: {
			cancel: {
				label: __( 'キャンセル', 'hametuha' ),
				className: 'btn-default'
			},
			ok: {
				label: deletable ? __( '実行', 'hametuha' ) : __( 'OK', 'hametuha' ),
				className: deletable ? 'btn-danger' : 'btn-success',
				callback: callback
			}
		}
	} );
};
