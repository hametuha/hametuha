/*!
 * Hashboard Payment Screen - React version
 *
 * @handle hametuha-hb-payment-table
 * @deps wp-element, wp-i18n, wp-api-fetch, hb-components-bar-chart, hb-components-loading, hb-components-month-selector, hb-plugins-toast
 */

const { createRoot, useState, useEffect, useCallback } = wp.element;
const { __ } = wp.i18n;

// ============================================
// Utility Functions
// ============================================

/**
 * Format number as Japanese Yen
 * @param {number|string} value
 * @returns {string}
 */
const formatMoney = ( value ) => {
	const num = parseInt( value, 10 );
	if ( isNaN( num ) ) {
		return '¥0';
	}
	return '¥' + num.toLocaleString( 'ja-JP' );
};

/**
 * Format currency with appropriate symbol
 * @param {string} value
 * @param {string} currency
 * @returns {string}
 */
const formatCurrency = ( value, currency ) => {
	const price = value.toString().split( '.' );
	const intPart = parseInt( price[ 0 ], 10 ).toLocaleString( 'ja-JP' );
	let prefix = '¥';
	let hasDecimal = false;

	switch ( currency ) {
		case 'USD':
			prefix = '$';
			hasDecimal = true;
			break;
		case 'GBP':
			prefix = '£';
			hasDecimal = true;
			break;
		case 'EUR':
			prefix = '€';
			hasDecimal = true;
			break;
		case 'JPY':
		default:
			prefix = '¥';
			break;
	}

	const decimal = hasDecimal && price[ 1 ] ? '.' + price[ 1 ] : '';
	return prefix + intPart + decimal;
};

/**
 * Get unit suffix based on store type
 * @param {string} store
 * @returns {string}
 */
const getUnitSuffix = ( store ) => {
	return store === 'KENP' ? 'P' : '部';
};

/**
 * Get display label for store
 * @param {string} store
 * @returns {string}
 */
const getStoreLabel = ( store ) => {
	return store === 'Amazon' ? 'KDP' : store;
};

/**
 * Format date string
 * @param {string} dateStr
 * @param {string} format - 'MM', 'DD', 'YYYY/MM/DD'
 * @returns {string}
 */
const formatDate = ( dateStr, format ) => {
	if ( ! dateStr || dateStr === '0000-00-00 00:00:00' ) {
		return '---';
	}
	const date = new Date( dateStr );
	if ( isNaN( date.getTime() ) ) {
		return '---';
	}
	switch ( format ) {
		case 'MM':
			return String( date.getMonth() + 1 ).padStart( 2, '0' );
		case 'DD':
			return String( date.getDate() ).padStart( 2, '0' );
		case 'YYYY/MM/DD':
			return `${ date.getFullYear() }/${ String( date.getMonth() + 1 ).padStart( 2, '0' ) }/${ String( date.getDate() ).padStart( 2, '0' ) }`;
		default:
			return dateStr;
	}
};

/**
 * Convert price to JPY equivalent
 * @param {number} price
 * @param {string} currency
 * @returns {number}
 */
const exchangeToJPY = ( price, currency ) => {
	const rates = {
		JPY: 1,
		GBP: 150,
		EUR: 130,
		USD: 100,
	};
	return price * ( rates[ currency ] || 100 );
};

/**
 * Get number of days in a month
 * @param {number} year
 * @param {number} month
 * @returns {number}
 */
const getDaysInMonth = ( year, month ) => {
	const days = [ 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 ];
	// Leap year check
	if ( month === 2 && ( ( year % 4 === 0 && year % 100 !== 0 ) || year % 400 === 0 ) ) {
		return 29;
	}
	return days[ month - 1 ];
};

/**
 * Show toast message
 * @param {string} message
 * @param {string} type - 'success' | 'error'
 */
const showToast = ( message, type = 'error' ) => {
	if ( window.Hashboard && window.Hashboard.toast ) {
		const icon = type === 'error' ? 'close' : 'check';
		window.Hashboard.toast( `<i class="material-icons ${ type }">${ icon }</i>${ message }`, 4000 );
	}
};

// ============================================
// Custom Components (Bootstrap 5)
// ============================================

/**
 * Month selector component using Bootstrap 5
 */
const MonthSelector = ( { label, minYear = 2015, initialYear, initialMonth, onDateUpdated } ) => {
	const currentYear = new Date().getFullYear();
	const [ year, setYear ] = useState( initialYear );
	const [ month, setMonth ] = useState( initialMonth );

	// Generate year options
	const yearOptions = [];
	for ( let y = currentYear; y >= minYear; y-- ) {
		yearOptions.push( y );
	}

	// Month names
	const monthNames = [
		'1月', '2月', '3月', '4月', '5月', '6月',
		'7月', '8月', '9月', '10月', '11月', '12月',
	];

	const handleSubmit = () => {
		if ( onDateUpdated ) {
			onDateUpdated( year, month );
		}
	};

	return (
		<div className="row g-2 mb-3" title={ label }>
			<div className="col-auto">
				<select
					className="form-select"
					value={ year }
					onChange={ ( e ) => setYear( parseInt( e.target.value, 10 ) ) }
				>
					{ yearOptions.map( ( y ) => (
						<option key={ y } value={ y }>{ y }年</option>
					) ) }
				</select>
			</div>
			<div className="col-auto">
				<select
					className="form-select"
					value={ month }
					onChange={ ( e ) => setMonth( parseInt( e.target.value, 10 ) ) }
				>
					{ monthNames.map( ( name, index ) => (
						<option key={ index + 1 } value={ index + 1 }>{ name }</option>
					) ) }
				</select>
			</div>
			<div className="col-auto">
				<button
					type="button"
					className="btn btn-secondary"
					onClick={ handleSubmit }
				>
					更新
				</button>
			</div>
		</div>
	);
};

// ============================================
// Custom Hooks
// ============================================

/**
 * Get initial year and month from URL hash or current date
 */
const useInitialDate = () => {
	const now = new Date();
	let year = now.getFullYear();
	let month = now.getMonth() + 1;

	const hash = window.location.hash;
	const yearMatch = hash.match( /year=(\d{4})/ );
	const monthMatch = hash.match( /month=(\d{1,2})/ );

	if ( yearMatch ) {
		year = parseInt( yearMatch[ 1 ], 10 );
	}
	if ( monthMatch ) {
		month = parseInt( monthMatch[ 1 ], 10 );
	}

	return { year, month };
};

// ============================================
// SalesHistory Component (電子書籍売上)
// ============================================

const SalesHistory = ( { endpoint } ) => {
	const { year: initialYear, month: initialMonth } = useInitialDate();
	const [ loading, setLoading ] = useState( true );
	const [ total, setTotal ] = useState( 0 );
	const [ records, setRecords ] = useState( [] );
	const [ chartData, setChartData ] = useState( null );
	const [ chartOptions ] = useState( {
		responsive: true,
		maintainAspectRatio: false,
		scales: {
			y: {
				position: 'left',
				title: {
					display: true,
					text: '売上高 (円)',
				},
			},
			y1: {
				position: 'right',
				title: {
					display: true,
					text: '販売数',
				},
				grid: {
					drawOnChartArea: false,
				},
			},
		},
		plugins: {
			tooltip: {
				enabled: true,
				mode: 'index',
				callbacks: {
					label: function( context ) {
						const label = context.dataset.label || '';
						const value = context.raw;
						if ( context.datasetIndex === 0 ) {
							return `${ label }: ${ formatMoney( value ) }`;
						}
						const suffix = getUnitSuffix( label );
						return `${ label }: ${ value }${ suffix }`;
					},
				},
			},
		},
	} );

	const fetchData = useCallback( async ( year, month ) => {
		setLoading( true );
		try {
			const response = await wp.apiFetch( {
				path: wp.url.addQueryArgs( endpoint, { year, month } ),
			} );

			setTotal( response.total );
			setRecords( response.records );

			// Build chart data
			if ( ! response.records.length ) {
				setChartData( null );
				return;
			}

			const daysInMonth = getDaysInMonth( year, month );
			const labels = Array.from( { length: daysInMonth }, ( _, i ) => i + 1 );
			const priceData = new Array( daysInMonth ).fill( 0 );
			const datasets = {};

			response.records.forEach( ( record ) => {
				const day = parseInt( record.date.replace( /^\d{4}-\d{2}-/, '' ), 10 ) - 1;
				priceData[ day ] += exchangeToJPY( parseFloat( record.royalty ), record.currency );

				const storeLabel = getStoreLabel( record.store );
				if ( ! datasets[ storeLabel ] ) {
					datasets[ storeLabel ] = new Array( daysInMonth ).fill( 0 );
				}
				datasets[ storeLabel ][ day ] += parseInt( record.unit, 10 );
			} );

			const colors = [ '#039BE5', '#388E3C', '#7B1FA2', '#F57C00', '#0097A7', '#455A64' ];
			const chartDatasets = [
				{
					type: 'line',
					label: '売上高',
					yAxisID: 'y',
					data: priceData,
					backgroundColor: 'rgba(248,121,121,1)',
					borderColor: 'rgba(248,121,121,.6)',
					fill: false,
				},
			];

			let colorIndex = 0;
			for ( const prop in datasets ) {
				if ( datasets.hasOwnProperty( prop ) ) {
					chartDatasets.push( {
						type: 'bar',
						label: prop,
						yAxisID: 'y1',
						data: datasets[ prop ],
						backgroundColor: colors[ colorIndex % colors.length ],
					} );
					colorIndex++;
				}
			}

			setChartData( { labels, datasets: chartDatasets } );
		} catch ( error ) {
			showToast( error.message || 'データの取得ができませんでした。' );
			setRecords( [] );
			setChartData( null );
		} finally {
			setLoading( false );
		}
	}, [ endpoint ] );

	// Initial fetch
	useEffect( () => {
		fetchData( initialYear, initialMonth );
	}, [] ); // eslint-disable-line react-hooks/exhaustive-deps

	const handleDateUpdate = useCallback( ( year, month ) => {
		fetchData( year, month );
	}, [ fetchData ] );

	// Get components from hashboard (hb.components)
	const MonthSelector = globalThis.hb?.components?.MonthSelector;
	const BarChart = globalThis.hb?.components?.BarChart;
	const Loading = globalThis.hb?.components?.LoadingIndicator;

	return (
		<>
			{ MonthSelector && (
				<MonthSelector
					label="日付"
					minYear={ 2015 }
					initialYear={ initialYear }
					initialMonth={ initialMonth }
					onDateUpdated={ ( year, month ) => handleDateUpdate( parseInt( year, 10 ), parseInt( month, 10 ) ) }
				/>
			) }

			{ chartData && BarChart && (
				<div className="hb-chart hb-chart-line">
					<BarChart chartData={ chartData } options={ chartOptions } />
				</div>
			) }

			<table className={ `table table-striped highlight${ loading ? ' loading' : '' }` }>
				<thead>
					<tr>
						<th className="cell-2">月</th>
						<th className="cell-2">日</th>
						<th className="text-start">商品</th>
						<th className="text-end">ロイヤリティ</th>
						<th className="text-end">数量</th>
						<th className="text-start">ストア</th>
						<th className="text-start">販売形態</th>
						<th className="text-start">種別</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th colSpan="3">&nbsp;</th>
						<th className="text-end">{ formatMoney( total ) }</th>
						<th colSpan="4">&nbsp;</th>
					</tr>
				</tfoot>
				<tbody>
					{ loading && Loading && (
						<tr>
							<td colSpan="8" className="text-center">
								<Loading />
							</td>
						</tr>
					) }
					{ ! loading && records.length === 0 && (
						<tr>
							<td className="error text-center disabled" colSpan="8">
								記録がありません。
							</td>
						</tr>
					) }
					{ ! loading && records.map( ( record, index ) => (
						<tr key={ index }>
							<td className="text-center">{ formatDate( record.date, 'MM' ) }</td>
							<td className="text-center">{ formatDate( record.date, 'DD' ) }</td>
							<td>{ record.post_title }</td>
							<td className="text-end">{ formatCurrency( record.royalty, record.currency ) }</td>
							<td className="text-end">{ record.unit }{ getUnitSuffix( record.store ) }</td>
							<td className="text-start">{ record.place }</td>
							<td className="text-start">{ record.type }</td>
							<td className="text-start">{ getStoreLabel( record.store ) }</td>
						</tr>
					) ) }
				</tbody>
			</table>
		</>
	);
};

// ============================================
// SalesRewards Component (確定報酬・報酬履歴)
// ============================================

const SalesRewards = ( { endpoint, isDeposit } ) => {
	const { year: initialYear, month: initialMonth } = useInitialDate();
	const [ loading, setLoading ] = useState( true );
	const [ total, setTotal ] = useState( 0 );
	const [ tax, setTax ] = useState( 0 );
	const [ available, setAvailable ] = useState( false );
	const [ records, setRecords ] = useState( [] );

	const fetchData = useCallback( async ( year, month, status ) => {
		setLoading( true );
		try {
			const response = await wp.apiFetch( {
				path: wp.url.addQueryArgs( endpoint, { year, month, status } ),
			} );

			setTotal( response.total );
			setTax( response.deducting );
			setAvailable( response.enough );
			setRecords( response.records );
		} catch ( error ) {
			showToast( error.message || 'データの取得ができませんでした。' );
			setRecords( [] );
		} finally {
			setLoading( false );
		}
	}, [ endpoint ] );

	// Initial fetch
	useEffect( () => {
		if ( isDeposit ) {
			// For deposit: get all unpaid
			fetchData( '0000', 0, 0 );
		} else {
			// For rewards: get paid for selected month
			fetchData( initialYear, initialMonth, 1 );
		}
	}, [] ); // eslint-disable-line react-hooks/exhaustive-deps

	const handleDateUpdate = useCallback( ( year, month ) => {
		fetchData( year, month, 1 );
	}, [ fetchData ] );

	const MonthSelector = globalThis.hb?.components?.MonthSelector;
	const Loading = globalThis.hb?.components?.LoadingIndicator;

	return (
		<>
			{ ! isDeposit && MonthSelector && (
				<MonthSelector
					label="日付"
					minYear={ 2015 }
					initialYear={ initialYear }
					initialMonth={ initialMonth }
					onDateUpdated={ ( year, month ) => handleDateUpdate( parseInt( year, 10 ), parseInt( month, 10 ) ) }
				/>
			) }

			<table className={ `table table-striped highlight${ loading ? ' loading' : '' }` }>
				<thead>
					<tr>
						<th className="cell-2 text-end">#</th>
						<th className="text-start">適用</th>
						<th className="text-start">単価</th>
						<th className="cell-3 text-end">数量</th>
						<th className="text-end">消費税</th>
						<th className="text-end">源泉徴収</th>
						<th className="text-end">入金額</th>
						<th className="text-end">登録日</th>
						{ ! isDeposit && <th className="text-end">支払日</th> }
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th colSpan="5">&nbsp;</th>
						<th className="text-end">{ formatMoney( tax ) }</th>
						<th className="text-end">{ formatMoney( total ) }</th>
						{ ! isDeposit ? (
							<th>&nbsp;</th>
						) : (
							<th className="text-end">
								{ available ? (
									<span className="text-success">
										<i className="material-icons">check</i>
										振込予定
									</span>
								) : (
									<span className="text-danger">
										<i className="material-icons">close</i>
										金額不足
									</span>
								) }
							</th>
						) }
						{ ! isDeposit && <th>&nbsp;</th> }
					</tr>
				</tfoot>
				<tbody>
					{ loading && Loading && (
						<tr>
							<td colSpan={ isDeposit ? 8 : 9 } className="text-center">
								<Loading />
							</td>
						</tr>
					) }
					{ ! loading && records.length === 0 && (
						<tr>
							<td className="error text-center disabled" colSpan={ isDeposit ? 8 : 9 }>
								記録がありません。
							</td>
						</tr>
					) }
					{ ! loading && records.map( ( record, index ) => (
						<tr key={ index }>
							<th>{ record.revenue_id }</th>
							<td><strong>【{ record.label }】</strong>{ record.description }</td>
							<td className="text-end">{ formatMoney( record.price ) }</td>
							<td className="text-end">{ record.unit }</td>
							<td className="text-end">{ formatMoney( record.tax ) }</td>
							<td className="text-end">{ formatMoney( record.deducting ) }</td>
							<td className="text-end">{ formatMoney( record.total ) }</td>
							<td className="text-end">{ formatDate( record.created, 'YYYY/MM/DD' ) }</td>
							{ ! isDeposit && (
								<td className="text-end">
									{ record.fixed === '0000-00-00 00:00:00' ? '---' : formatDate( record.fixed, 'YYYY/MM/DD' ) }
								</td>
							) }
						</tr>
					) ) }
				</tbody>
			</table>
		</>
	);
};

// ============================================
// SalesPayments Component (入金履歴)
// ============================================

const SalesPayments = ( { endpoint } ) => {
	const currentYear = new Date().getFullYear();
	const [ selectedYear, setSelectedYear ] = useState( currentYear );
	const [ loading, setLoading ] = useState( true );
	const [ total, setTotal ] = useState( 0 );
	const [ tax, setTax ] = useState( 0 );
	const [ records, setRecords ] = useState( [] );

	// Generate year options
	const yearOptions = [];
	for ( let y = currentYear; y >= 2015; y-- ) {
		yearOptions.push( y );
	}

	const fetchData = useCallback( async ( year ) => {
		setLoading( true );
		try {
			const response = await wp.apiFetch( {
				path: wp.url.addQueryArgs( endpoint, { year } ),
			} );

			setTotal( response.total );
			setTax( response.deducting );
			setRecords( response.records );
		} catch ( error ) {
			showToast( error.message || 'データの取得ができませんでした。' );
			setRecords( [] );
		} finally {
			setLoading( false );
		}
	}, [ endpoint ] );

	// Initial fetch
	useEffect( () => {
		fetchData( selectedYear );
	}, [] ); // eslint-disable-line react-hooks/exhaustive-deps

	const handleYearChange = ( e ) => {
		const year = parseInt( e.target.value, 10 );
		setSelectedYear( year );
		fetchData( year );
	};

	const Loading = globalThis.hb?.components?.LoadingIndicator;

	return (
		<>
			<div className="mb-3">
				<select
					className="form-select"
					value={ selectedYear }
					onChange={ handleYearChange }
				>
					{ yearOptions.map( ( year ) => (
						<option key={ year } value={ year }>
							{ year }年
						</option>
					) ) }
				</select>
			</div>

			<table className={ `table table-striped highlight${ loading ? ' loading' : '' }` }>
				<thead>
					<tr>
						<th className="cell-2">月</th>
						<th className="cell-2">日</th>
						<th className="text-start">支払先</th>
						<th className="text-end">入金額</th>
						<th className="text-end">源泉徴収</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th colSpan="3">&nbsp;</th>
						<th className="text-end">{ formatMoney( total ) }</th>
						<th className="text-end">{ formatMoney( tax ) }</th>
					</tr>
				</tfoot>
				<tbody>
					{ loading && Loading && (
						<tr>
							<td colSpan="5" className="text-center">
								<Loading />
							</td>
						</tr>
					) }
					{ ! loading && records.length === 0 && (
						<tr>
							<td className="error text-center disabled" colSpan="5">
								記録がありません。
							</td>
						</tr>
					) }
					{ ! loading && records.map( ( record, index ) => (
						<tr key={ index }>
							<td className="text-center">{ formatDate( record.fixed, 'MM' ) }</td>
							<td className="text-center">{ formatDate( record.fixed, 'DD' ) }</td>
							<td>{ record.display_name }</td>
							<td className="text-end">{ formatMoney( record.total ) }</td>
							<td className="text-end">{ formatMoney( record.deducting ) }</td>
						</tr>
					) ) }
				</tbody>
			</table>
		</>
	);
};

// ============================================
// Main Container Component
// ============================================

const SalesContainer = ( { endpoint, slug } ) => {
	switch ( slug ) {
		case 'history':
			return <SalesHistory endpoint={ endpoint } />;
		case 'deposit':
			return <SalesRewards endpoint={ endpoint } isDeposit={ true } />;
		case 'rewards':
			return <SalesRewards endpoint={ endpoint } isDeposit={ false } />;
		case 'payments':
			return <SalesPayments endpoint={ endpoint } />;
		default:
			return <div className="alert alert-danger">{ __( '不明なページタイプです。', 'hametuha' ) } { slug }</div>;
	}
};

// ============================================
// Initialize
// ============================================

const container = document.getElementById( 'sales-container' );
if ( container ) {
	const endpoint = container.dataset.endpoint || '';
	const slug = container.dataset.slug || 'history';
	createRoot( container ).render( <SalesContainer endpoint={ endpoint } slug={ slug } /> );
}
