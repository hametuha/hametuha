/*!
 * Toast ui.
 * @package hametuha
 * @handle hametuha-toast
 * @deps hametuha-components, wp-i18n
 */

const { __ } = wp.i18n;

/**
 * Ensure container.
 *
 * @returns {HTMLDivElement}
 */
const getContainer = () => {
	let div = document.getElementById( 'hametuha-toast-container' );
	if ( ! div ) {
		div = document.createElement( 'div' );
		div.setAttribute( 'id', 'hametuha-toast-container' );
		div.classList.add( 'hametuha-toast-container' );
		document.getElementsByTagName( 'body' )[ 0 ].appendChild( div );
	}
	return div;
}

/**
 * Remove div element.
 *
 * @param {HTMLDivElement} toast
 */
const removeToast = ( toast ) => {
	toast.classList.remove('hametuha-toast-on');
	setTimeout(function(){
		toast.remove();
	}, 300);
};

/**
 * Display toast.
 *
 * @param {string} message
 * @param {string} type
 * @param {string} label   Label.
 * @param {number} delay
 * @return {HTMLDivElement}
 */
const toast = ( message, type='success', label='', delay= 7000 ) => {
	label = label || __( 'お知らせ', 'hametuha' );
	// Create Wrapper.
	const toast = document.createElement( 'div' );
	toast.className = [ 'hametuha-toast', 'hametuha-toast-' + type ].join( ' ' );
	// Create button.
	const button = document.createElement( 'button' );
	button.classList.add( 'hametuha-toast-close');
	button.setAttribute( 'type', 'button' );
	button.setAttribute( 'data-dismiss', 'alert' );
	button.setAttribute( 'aria-label', __( '閉じる', 'hametuha' ) );
	button.innerHTML = '<span aria-hidden="true">&times;</span>';
	// Create label
	const strong = document.createElement( 'strong' );
	strong.appendChild( document.createTextNode( label ) );
	// Create Header
	const header = document.createElement( 'header' );
	header.classList.add( 'hametuha-toast-header' );
	header.appendChild( strong );
	header.appendChild( button );
	toast.appendChild( header );
	// Create message.
	const p = document.createElement( 'p' );
	p.appendChild( document.createTextNode( message ) );
	toast.appendChild( p );
	// Add to body.
	getContainer().appendChild( toast );
	// Set timeout to display.
	setTimeout(function(){
		toast.classList.add('hametuha-toast-on');
	}, 10);
	// Add click handler.
	button.addEventListener( 'click', function(){
		removeToast( toast );
	} );
	// Remove alert because of timeout.
	setTimeout(function(){
		removeToast( toast );
	}, delay );
	return toast;
}

wp.hametuha.toast = toast;
