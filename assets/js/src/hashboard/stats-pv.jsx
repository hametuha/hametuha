/*!
 * Hashboard Stats PV
 *
 * @handle hametuha-hb-stats-pv
 * @deps hashboard,hb-components-bar-chart,hb-components-month-selector, hb-plugins-date
 * @package hametuha
 */

/*global Vue: true*/

const $ = jQuery;

const app = new Vue( {
	el: '#access-container',
	data: function () {
		const pad2 = ( num ) => {
			return ( '0' + num ).slice( -2 );
		};
		const now = new Date();
		const year = now.getFullYear();
		const month = pad2( now.getMonth() + 1 );
		const day = pad2( now.getDate() );
		now.setDate( now.getDate() - 30 );
		return {
			loading: false,
			from: [ now.getFullYear(), pad2( now.getMonth() + 1 ), pad2( now.getDate() ) ].join( '-' ),
			to: [ year, month, day ].join( '-' ),
			rankings: [],
			records: [],
			chartData: {},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				tooltips: {
					enabled: true,
					mode: 'index',
					callbacks: {
						label: function ( tooltipItems, data ) {
							return tooltipItems.yLabel + 'PV（' + data.datasets[ tooltipItems.datasetIndex ].label + '）';
						}
					}
				},
				scales: {
					yAxes: [ {
						stacked: true
					} ]
				}
			}
		};
	},
	computed: {},

	mounted: function () {
		this.fetch();
	},
	methods: {
		filledLabels: function ( from, to ) {
			var start = new Date( from );
			var end = new Date( to );
			var labels = [];
			for ( ; start <= end; start.setDate( start.getDate() + 1 ) ) {
				labels.push( start.getFullYear() + '-' + ( '0' + ( start.getMonth() + 1 ) ).slice( -2 ) + '-' + ( '0' + start.getDate() ).slice( -2 ) );
			}
			return labels;
		},

		/**
		 * Get empty label
		 *
		 * @param length
		 * @returns {Array}
		 */
		skeleton: function ( length ) {
			var array = [];
			for ( var i = 0; i < length; i++ ) {
				array.push( 0 );
			}
			return array;
		},

		dateChangeHandler: function () {
			this.fetch();
		},


		fillDataSet: function ( dataSets, labels, type, date, pv ) {
			if ( !dataSets[ type ] ) {
				dataSets[ type ] = this.skeleton( labels.length );
			}
			var index = labels.indexOf( date );
			if ( index > -1 ) {
				dataSets[ type ][ index ] += pv;
			}
		},

		fetch: function () {
			this.loading = true;
			var self = this;
			$.hbRest( 'GET', $( '#access-container' ).attr( 'data-endpoint' ), {
				from: this.from,
				to: this.to
			} ).done( function ( response ) {
				// Set ranking
				var rankings = [];
				var curRank, j;
				for ( var i = 0, l = response.rankings.length; i < l; i++ ) {
					curRank = 0;
					for ( j = 0; j < l; j++ ) {
						if ( response.rankings[ j ].pv > response.rankings[ i ].pv ) {
							curRank++;
						} else {
							break;
						}
					}
					response.rankings[ i ].rank = curRank + 1;
				}
				self.rankings = response.rankings;
				// Create graph
				var chartData = {};
				var labels = self.filledLabels( response.start, response.end );
				var data_sets = {};
				$.each( response.records, function ( index, record ) {
					self.fillDataSet( data_sets, labels, record.post_type, record.date, record.pv );
				} );

				chartData.labels = labels;
				chartData.datasets = [];
				var colors = [
					[ 255, 12, 62 ],
					[ 0, 153, 232 ],
					[ 247, 124, 0 ],
					[ 95, 125, 140 ],
					[ 52, 143, 55 ],
					[ 255, 59, 0 ],
					[ 124, 19, 164 ],
					[ 68, 90, 101 ],
					[ 0, 184, 214 ],
					[ 110, 76, 64 ]
				];
				var colorIndex = 0;
				for ( var prop in data_sets ) {
					if ( !data_sets.hasOwnProperty( prop ) ) {
						continue;
					}
					var color = colors[ colorIndex ].join( ', ' );
					chartData.datasets.push( {
						type: 'line',
						lineTension: 0,
						label: prop,
						data: data_sets[ prop ],
						backgroundColor: 'rgb(' + color + ')',
						borderColor: 'rgba(' + color + ', .6)'
					} );
					colorIndex++;
				}
				self.chartData = chartData;
				self.records = response.records;
			} ).fail( $.hbRestError() ).always( function () {
				self.loading = false;
			} );
		}
	}
} );
