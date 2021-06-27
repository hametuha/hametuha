/*!
 * Hametuha Alert.
 *
 * @handle hametuha-alert
 * @deps jquery
 */

if ( ! window.Hametuha ) {
	window.Hametuha = {};
}
const { Hametuha } = window;
const $ = jQuery;

/**
 * Display global message.
 *
 * @param {string} message
 * @param {string} [type]
 * @param {number} [delay]
 */
Hametuha.alert = function ( message, type, delay ) {
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
	body = '<div class="alert alert-' + typeName + ' alert-dismissible alert-sticky" role="alert">' +
		'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
		message +
		'</div>';
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
};
