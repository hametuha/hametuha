/*!
 * Components
 *
 * @handle hametuha-components
 * @deps wp-element
 */

if ( ! window.wp.hametuha ) {
	window.wp.hametuha = {};
}

/**
 * Display class anmes from object.
 *
 * @param {object} classes key is class name, value is boolean.
 * @returns {string} Class names.
 */
window.wp.hametuha.classNames = ( classes )	=> {
	const attr = [];
	Object.keys( classes ).forEach( (key) => {
		if ( classes[ key ] ) {
			attr.push( key );
		}
	});
	return attr.join( ' ' );
};
