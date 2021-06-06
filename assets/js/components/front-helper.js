/*!
 * Front page helper
 *
 * @handle hametuha-front
 * @deps jquery-masonry, imagesloaded, chart-js
 */

/*global Chart: true*/
/*global HametuhaGenreStatic: true*/

const $ = jQuery;

$( document ).ready( function () {

	// レーダーチャート
	const radar = $( '#genre-context' );
	const data = {
		labels: [],
		datasets: [ {
			data: [],
			backgroundColor: []
		} ]
	};
	if ( radar.length ) {
		// データを加工する
		$.each( HametuhaGenreStatic.categories, function ( index, cat ) {
			if ( index > 10 ) {
				return false;
			}
			data.labels.push( cat.name );
			data.datasets[ 0 ].data.push( parseInt( cat.count, 10 ) );
			data.datasets[ 0 ].backgroundColor.push( 'rgba(255, 0, 0, ' + Math.min( 1, Math.round( ( cat.count / HametuhaGenreStatic.total ) * 0.8 * 10 ) / 10 + 0.2 ) + ')' );
		} );
		const ctx = radar.get( 0 ).getContext( '2d' );
		new Chart( ctx, {
			type: 'doughnut',
			data: data
		} );
	}

	// masonry
	const container = $( '.frontpage-widget' );
	// initialize Masonry after all images have loaded
	container.imagesLoaded( function () {
		container.masonry( {
			itemSelector: '.col-sm-4'
		} );
	} );
} );
