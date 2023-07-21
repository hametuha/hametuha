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
 * Display class names from object.
 *
 * @param {object} classes key is class name, value is boolean.
 * @returns {string} Class names.
 */
const classNames = ( classes )	=> {
	const attr = [];
	Object.keys( classes ).forEach( (key) => {
		if ( classes[ key ] ) {
			attr.push( key );
		}
	});
	return attr.join( ' ' );
};
window.wp.hametuha.classNames = classNames;


/**
 * Convert date object to string.
 *
 * @param {Date} date
 * @return {string}
 */
const toDateTime = ( date ) => {
	const str = [ date.getFullYear() ];
	for ( const s of [ date.getMonth() + 1, date.getDate() ] ) {
		str.push( ( '0' + s ).slice( -2 ) );
	}
	return str.join( '-' );
};
window.wp.hametuha.toDateTime = toDateTime;
