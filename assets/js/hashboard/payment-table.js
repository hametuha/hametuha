/*!
 * Hashboard payment table
 *
 * @handle hametuha-hb-payment-table
 * @deps hashboard,hb-components-bar-chart,hb-components-pagination,hb-components-month-selector,hb-filters-moment
 */

const $ = jQuery;

const displayMoney = function ( value ) {
	value = parseInt( value, 10 );
	return '¥' + value.toString().replace( /([0-9]+?)(?=(?:[0-9]{3})+$)/g, '$1,' );
};

// Looks like money
Vue.filter( 'monetize', displayMoney );

Vue.filter( 'currency', function ( value, currency ) {
	const price = value.split( '.' );
	let int = parseInt( price ).toString().replace( /([0-9]+?)(?=(?:[0-9]{3})+$)/g, '$1,' );
	let prefix, join = false;
	switch ( currency ) {
		case 'USD':
			prefix = '$';
			join = true;
			break;
		case 'GBP':
			prefix = '£';
			join = true;
			break;
		case 'EUR':
			prefix = '€';
			join = true;
			break;
		case 'JPY':
		default:
			prefix = '¥';
			break;
	}
	if ( join ) {
		int += '.' + price[ 1 ];
	}
	return prefix + int;
} );

const getUnitSuffix = function ( store ) {
	switch ( store ) {
		case 'KENP':
			return 'P';
		default:
			return '部';
	}
};

Vue.filter( 'addSuffix', function ( value, store ) {
	return value + getUnitSuffix( store );
} );

const getLabel = function ( string ) {
	switch ( string ) {
		case 'Amazon':
			return 'KDP';
		default:
			return string;
	}
};

Vue.filter( 'labeling', getLabel );


let curMonth, curYear;
if ( location.hash.match( /month=(\d{2})/ ) ) {
	curMonth = ( '0' + RegExp.$1 ).slice( -2 );
} else {
	curMonth = ( '0' + ( new Date().getMonth() + 1 ) ).slice( -2 );
}
if ( location.hash.match( /year=(\d{4})/ ) ) {
	curYear = RegExp.$1;
} else {
	curYear = new Date().getFullYear();
}

new Vue( {
	el: '#sales-container',
	data: {
		loading: true,
		total: 0,
		tax: 0,
		available: false,
		currentYear: curYear,
		currentMonth: curMonth,
		records: [],
		options: {
			responsive: true,
			maintainAspectRatio: false,
			scales: {
				yAxes: [
					{
						id: "y-axis-price",
						type: "linear",
						position: "left"
					},
					{
						id: "y-axis-copy",
						type: "linear",
						position: "right"
					},
					{
						id: "y-axis-page",
						type: "linear",
						position: "right"
					}
				]
			},
			tooltips: {
				enabled: true,
				mode: 'single',
				callbacks: {
					label: function ( tooltipItems, data ) {
						if ( tooltipItems.datasetIndex ) {
							return tooltipItems.yLabel + getUnitSuffix( data.datasets[ tooltipItems.datasetIndex ].label );
						} 
							return displayMoney( tooltipItems.yLabel );
						
					}
				}
			}
		},
		chartData: {}
	},
	computed: {
		endpoint: function () {
			return $( '#sales-container' ).attr( 'data-endpoint' );
		},
		curMonth: function () {
			return parseInt( this.curMonth, 10 );
		},
		curYear: function () {
			return parseInt( this.curYear, 10 );
		}
	},
	methods: {

		errorHandler: function () {
			return function ( response ) {
				let msg = 'データの取得ができませんでした。';
				if ( response.responseJSON && response.responseJSON.message ) {
					msg = response.responseJSON.message;
				}
				Hashboard.toast( '<i class="material-icons error">close</i>' + msg, 4000 );
			};
		},

		fillDate: function ( data, index, value, end ) {
			if ( !data ) {
				data = [];
				for ( let i = 0; i < end; i++ ) {
					data.push( 0 );
				}
			}
			data[ index ] += value;
			return data;
		},

		exchange: function ( price, currency ) {
			let ratio;
			switch ( currency ) {
				case 'JPY':
					ratio = 1;
					break;
				case 'GBP':
					ratio = 150;
					break;
				case 'EUR':
					ratio = 130;
					break;
				default:
					ratio = 100;
					break;
			}
			return price * ratio;
		},

		getSales: function ( year, month ) {
			const self = this;
			this.loading = true;
			$.hbRest( 'GET', this.endpoint, {
				year: year,
				month: month
			} ).done( function ( response ) {
				self.total = response.total;
				self.records = response.records;
				const chartData = {};
				if ( !response.records.length ) {
					self.chartData = chartData;
					return;
				}
				const labels = [];
				const end = [ 31, ( ( year % 4 === 0 && year % 100 !== 0 ) ? 29 : 28 ), 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 ][ parseInt( month, 10 ) - 1 ];
				const datasets = {};
				let price = null;
				for ( let i = 1; i <= end; i++ ) {
					labels.push( i );
				}
				$.each( response.records, function ( index, record ) {
					const label = getLabel( record.store );
					const date = parseInt( record.date.replace( /^\d{4}-\d{2}-/, '' ), 10 ) - 1;
					price = self.fillDate( price, date, self.exchange( parseFloat( record.royalty ), record.currency ), end );
					datasets[ label ] = self.fillDate( datasets[ label ], date, parseInt( record.unit, 10 ), end );
				} );
				chartData.labels = labels;
				chartData.datasets = [
					{
						type: 'line',
						label: '売上高',
						yAxisID: 'y-axis-price',
						data: price,
						backgroundColor: 'rgba(248,121,121,1)',
						borderColor: 'rgba(248,121,121,.6)',
						fill: false
					}
				];
				const colors = [
					'#039BE5',
					'#388E3C',
					'#7B1FA2',
					'#F57C00',
					'#0097A7',
					'#455A64'
				];
				let colorIndex = 0;
				for ( const prop in datasets ) {
					if ( datasets.hasOwnProperty( prop ) ) {
						chartData.datasets.push( {
							type: 'bar',
							label: prop,
							yAxisID: 'KENP' === prop ? 'y-axis-page' : 'y-axis-copy',
							data: datasets[ prop ],
							backgroundColor: colors[ colorIndex ]
						} );
						colorIndex++;
					}
				}
				self.chartData = chartData;
			} ).fail( $.hbRestError() ).always( function () {
				self.loading = false;
			} );
		},

		getReward: function ( year, month, status ) {
			const self = this;
			this.loading = true;
			$.hbRest( 'GET', this.endpoint, {
				year: year,
				month: month,
				status: ( 'undefined' === typeof status ) ? 'all' : status
			} ).done( function ( response ) {
				self.total = response.total;
				self.tax = response.deducting;
				self.available = response.enough,
					self.records = response.records;
			} ).fail( $.hbRestError() ).always( function () {
				self.loading = false;
			} );
		},

		getPayments: function () {
			const self = this;
			this.loading = true;
			$.hbRest( 'GET', this.endpoint, {
				year: this.currentYear
			} ).done( function ( response ) {
				self.total = response.total;
				self.tax = response.deducting;
				self.records = response.records;
			} ).fail( $.hbRestError() ).always( function () {
				self.loading = false;
			} );
		}
	},

	mounted: function () {
		const date = new Date();
		switch ( $( '#sales-container' ).attr( 'data-slug' ) ) {
			case 'rewards':
				this.getReward( this.currentYear, this.currentMonth, 1 );
				break;
			case 'deposit':
				this.getReward( '0000', 0, 0 );
				break;
			case 'payments':
				this.getPayments();
				break;
			case 'history':
				this.getSales( this.currentYear, this.currentMonth );
				break;
		}
	}
} );
