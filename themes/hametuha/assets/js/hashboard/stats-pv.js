/*!
 * Hashboard Stats PV - React version
 *
 * @handle hametuha-hb-stats-pv
 * @deps wp-element, wp-api-fetch, wp-url, hb-components-bar-chart, hb-components-loading
 */

const { createRoot, useState, useEffect, useCallback } = wp.element;

// ============================================
// Utility Functions
// ============================================

/**
 * Pad number with leading zero
 * @param {number} num
 * @returns {string}
 */
const pad2 = ( num ) => ( '0' + num ).slice( -2 );

/**
 * Generate date string YYYY-MM-DD
 * @param {Date} date
 * @returns {string}
 */
const formatDateString = ( date ) => {
	return [
		date.getFullYear(),
		pad2( date.getMonth() + 1 ),
		pad2( date.getDate() ),
	].join( '-' );
};

/**
 * Generate array of date labels between two dates
 * @param {string} from - Start date YYYY-MM-DD
 * @param {string} to - End date YYYY-MM-DD
 * @returns {string[]}
 */
const filledLabels = ( from, to ) => {
	const start = new Date( from );
	const end = new Date( to );
	const labels = [];
	for ( ; start <= end; start.setDate( start.getDate() + 1 ) ) {
		labels.push( formatDateString( start ) );
	}
	return labels;
};

/**
 * Create empty array of zeros
 * @param {number} length
 * @returns {number[]}
 */
const skeleton = ( length ) => Array( length ).fill( 0 );

/**
 * Fill dataset with PV data
 * @param {Object} dataSets
 * @param {string[]} labels
 * @param {string} type
 * @param {string} date
 * @param {number} pv
 */
const fillDataSet = ( dataSets, labels, type, date, pv ) => {
	if ( ! dataSets[ type ] ) {
		dataSets[ type ] = skeleton( labels.length );
	}
	const index = labels.indexOf( date );
	if ( index > -1 ) {
		dataSets[ type ][ index ] += pv;
	}
};

// Chart colors
const CHART_COLORS = [
	[ 255, 12, 62 ],
	[ 0, 153, 232 ],
	[ 247, 124, 0 ],
	[ 95, 125, 140 ],
	[ 52, 143, 55 ],
	[ 255, 59, 0 ],
	[ 124, 19, 164 ],
	[ 68, 90, 101 ],
	[ 0, 184, 214 ],
	[ 110, 76, 64 ],
];

// ============================================
// StatsPV Component
// ============================================

const StatsPV = ( { endpoint } ) => {
	// Initialize dates (last 30 days)
	const now = new Date();
	const thirtyDaysAgo = new Date();
	thirtyDaysAgo.setDate( thirtyDaysAgo.getDate() - 30 );

	const [ loading, setLoading ] = useState( true );
	const [ fromDate, setFromDate ] = useState( formatDateString( thirtyDaysAgo ) );
	const [ toDate, setToDate ] = useState( formatDateString( now ) );
	const [ rankings, setRankings ] = useState( [] );
	const [ chartData, setChartData ] = useState( {} );

	// Chart options
	const chartOptions = {
		responsive: true,
		maintainAspectRatio: false,
		plugins: {
			tooltip: {
				enabled: true,
				mode: 'index',
				callbacks: {
					label: ( context ) => {
						return `${ context.parsed.y }PV（${ context.dataset.label }）`;
					},
				},
			},
		},
		scales: {
			y: {
				stacked: true,
			},
		},
	};

	const fetchData = useCallback( async ( from, to ) => {
		setLoading( true );
		try {
			const response = await wp.apiFetch( {
				path: wp.url.addQueryArgs( endpoint, { from, to } ),
			} );

			// Process rankings
			const processedRankings = response.rankings.map( ( item, i, arr ) => {
				let rank = 1;
				for ( let j = 0; j < arr.length; j++ ) {
					if ( arr[ j ].pv > item.pv ) {
						rank++;
					} else {
						break;
					}
				}
				return { ...item, rank };
			} );
			setRankings( processedRankings );

			// Process chart data
			const labels = filledLabels( response.start, response.end );
			const dataSets = {};

			response.records.forEach( ( record ) => {
				fillDataSet( dataSets, labels, record.post_type, record.date, record.pv );
			} );

			const datasets = [];
			let colorIndex = 0;
			for ( const prop in dataSets ) {
				if ( ! dataSets.hasOwnProperty( prop ) ) {
					continue;
				}
				const color = CHART_COLORS[ colorIndex % CHART_COLORS.length ].join( ', ' );
				datasets.push( {
					type: 'line',
					lineTension: 0,
					label: prop,
					data: dataSets[ prop ],
					backgroundColor: `rgb(${ color })`,
					borderColor: `rgba(${ color }, .6)`,
				} );
				colorIndex++;
			}

			setChartData( { labels, datasets } );
		} catch ( error ) {
			console.error( 'Failed to fetch stats:', error );
			setRankings( [] );
			setChartData( {} );
		} finally {
			setLoading( false );
		}
	}, [ endpoint ] );

	// Initial fetch
	useEffect( () => {
		fetchData( fromDate, toDate );
	}, [] ); // eslint-disable-line react-hooks/exhaustive-deps

	const handleDateChange = () => {
		fetchData( fromDate, toDate );
	};

	const BarChart = globalThis.hb?.components?.BarChart;
	const Loading = globalThis.hb?.components?.LoadingIndicator;

	return (
		<div className={ loading ? 'loading minHeight' : '' }>
			<div className="row g-2 align-items-end mb-3">
				<div className="col">
					<label htmlFor="user-pv-from">開始</label>
					<input
						id="user-pv-from"
						className="form-control"
						type="date"
						value={ fromDate }
						onChange={ ( e ) => setFromDate( e.target.value ) }
					/>
				</div>
				<div className="col">
					<label htmlFor="user-pv-to">終了</label>
					<input
						id="user-pv-to"
						className="form-control"
						type="date"
						value={ toDate }
						onChange={ ( e ) => setToDate( e.target.value ) }
					/>
				</div>
				<div className="col-auto">
					<button
						className="btn btn-primary"
						onClick={ handleDateChange }
					>
						日付指定
					</button>
				</div>
			</div>

			{ BarChart && chartData.datasets?.length > 0 && (
				<div style={ { height: '300px', marginBottom: '1rem' } }>
					<BarChart
						chartData={ chartData }
						options={ chartOptions }
					/>
				</div>
			) }

			<table className={ `table table-striped${ loading ? ' loading' : '' }` }>
				<thead>
					<tr>
						<th className="cell-2 text-end">#</th>
						<th className="text-start">タイトル</th>
						<th className="text-start">種別</th>
						<th className="text-end">PV</th>
					</tr>
				</thead>
				<tbody>
					{ loading && Loading && (
						<tr>
							<td colSpan="4" className="text-center">
								<Loading />
							</td>
						</tr>
					) }
					{ ! loading && rankings.length === 0 && (
						<tr>
							<td className="error text-center disabled" colSpan="4">
								記録がありません。
							</td>
						</tr>
					) }
					{ ! loading && rankings.map( ( ranking, index ) => (
						<tr key={ index }>
							<td className="text-end">{ ranking.rank }</td>
							<td className="text-start">
								<a className="link" href={ ranking.url }>
									{ ranking.title }
								</a>
							</td>
							<td className="text-start">{ ranking.type }</td>
							<td className="text-end">{ ranking.pv }</td>
						</tr>
					) ) }
				</tbody>
			</table>
		</div>
	);
};

// ============================================
// Initialize
// ============================================

const container = document.getElementById( 'access-container' );
if ( container ) {
	const endpoint = container.dataset.endpoint || '';
	createRoot( container ).render( <StatsPV endpoint={ endpoint } /> );
}
