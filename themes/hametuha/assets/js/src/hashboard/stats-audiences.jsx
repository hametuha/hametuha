/*!
 * Hametuha Dashboard - Stats
 *
 * @handle hametuha-hb-stats-audiences
 * @deps wp-element, hametuha-date-range, hametuha-toast, wp-i18n, hametuha-loading-indicator, wp-api-fetch, wp-url, recharts, hametuha-chart-colors
 */

const { render, createRoot, Component } = wp.element;
const { __ } = wp.i18n;
const { apiFetch } = wp;
const { addQueryArgs } = wp.url;
const { DateRange, toast, LoadingIndicator, toDateTime, chartColors } = wp.hametuha;
const { PieChart, Pie, Cell, CartesianGrid, Bar, BarChart, XAxis, YAxis, ResponsiveContainer, Legend, Tooltip } = Recharts;

// Get div elements.
const div = document.getElementById( 'hametuha-user-analytics' );
const target = div?.dataset?.target || '';
// Set default dates.
const to = new Date();
const from = new Date();
from.setDate( from.getDate() - 30 );


class StatsAudiences extends Component {

	constructor( props ) {
		super( props );
		this.state = {
			from: props.from,
			to: props.to,
			loading: false,
			gender: [],
		};
	}

	componentDidMount() {
		this.fetch();
	}

	fetch() {


		this.setState( { loading: true }, () => {
			apiFetch( {
				path: addQueryArgs( '/hametuha/v1/stats/audiences/me/', {
					from: this.state.from,
					to: this.state.to,
				} ),
			} ).then( res => {
				const newState = {};
				for ( const key of [ 'gender', 'generation', 'new', 'region' ] ) {
					newState[ key ] = this.convertData( key, res[ key ] );
				}
				this.setState( newState );
			} ).catch( res => {
				toast( res.message || __(　'データを取得できませんでした。あとでお試しください。', 'hametuha' ), 'danger', __( 'エラー', 'hametuha' ) );
			} ).finally( () => {
				this.setState( { loading: false } );
			} );
		} );
	}

	/**
	 * Convert data to chart data.
	 *
	 * @param {string} key
	 * @param {array} data
	 * @returns {*[]}
	 */
	convertData( key, data ) {
		const ret = [];
		let labels;
		switch( key ) {
			case 'gender':
				labels = {
					male: __( '男性', 'hametuha' ),
					female: __( '女性', 'hametuha' ),
					unknown: __( '不明', 'hametuha' )
				};
				Object.keys( labels ).forEach( ( key ) => {
					for ( const item of data ) {
						if ( key === item[0] ) {
							ret.push( {
								name: labels[ key ],
								value: parseInt( item[2] ),
							} );
							break;
						}
					}
				} );
				break;
			case 'region':
			case 'generation':
				for ( const item of data ) {
					if ( 'region' === key && item[2] < 5 ) {
						continue;
					}
					ret.push( {
						name: ( [ 'unknown', '(not set)'].indexOf( item[0] ) >= 0 ) ? __( '不明', 'hametuha' ) : item[0],
						value: parseInt( item[2] ),
					} );
				}
				break;
			case 'new':
				labels = {
					new: __( '新しい読者', 'hametuha' ),
					returning: __( '常連', 'hametuha' ),
					"(not set)": __( '不明', 'hametuha' ),
				};
				for ( const item of data ) {
					if ( labels[ item[0] ] ) {
						ret.push( {
							name: labels[ item[0] ],
							value: parseInt( item[2] ),
						} );
					}
				}
				break;
		}
		return ret;
	}

	render() {

		const { loading, generation, gender, region } = this.state;

		const noContent = () => <p className="text-muted" style={ { margin: '40px 0' } }>{ __( '該当するデータはありませんでした。', 'hametuha' ) }</p>;

		return (
			<div className="hb-stats hb-stats-audiences">

				<DateRange from={ this.state.from } to={ this.state.to } onChange={ (from, to ) => {
					// Compare date and call toast.
					if ( from > to ) {
						toast( __( '開始日が終了日よりも前になるように設定してください。', 'hametuha' ), 'danger', __( '日付範囲エラー', 'hametuha' ) );
					} else {
						this.setState( { from, to  }, () => this.fetch() );
					}
				} }></DateRange>

				<hr />

				<div className="row">
					<div className="col-12 col-md-6">
						<h3>{ __( '読者の性別', 'hametuha' ) }</h3>

						{ !! gender.length ? (
							<ResponsiveContainer width="100%" height={ 300 }>
								<PieChart width="100%" height={300}>
									<Legend verticalAlign="top" height={36}/>
									<Tooltip />
									<Pie
										data={ gender }
										cx="50%"
										cy="50%"
										innerRadius={60}
										outerRadius={80}
										dataKey="value"
									>
										{ gender.map( ( entry, index ) => (
											<Cell key={`cell-${index}`} fill={chartColors( index ) } unit={ __( '人', 'hametuha' ) } />
										) ) }
									</Pie>
								</PieChart>
							</ResponsiveContainer>
						) : (
							noContent()
						) }
					</div>

					<div className="col-12 col-md-6">
						<h3>{ __( '読者の年齢層', 'hametuha' ) }</h3>
						{ !! generation ? (
								<ResponsiveContainer width="100%" height={ 300 }>
									<BarChart width="100%" minHeight={ 300 } data={ generation }>
										<CartesianGrid strokeDasharray="3 3" />
										<XAxis dataKey="name" />
										<YAxis />
										<Tooltip />
										<Bar dataKey="value" fill={ chartColors(0) } unit={ __( '人', 'hametuha' ) } />
									</BarChart>
								</ResponsiveContainer>
						) : (
							noContent()
						) }
					</div>
				</div>

				<hr />

				<div className="row">

					<div className="col-12 col-md-6">
						<h3>{ __( 'リピート率', 'hametuha' ) }</h3>

						<div className="mt-4 mb-4 alert alert-secondary" role="alert">
							{ __( '「新しい読者」は破滅派にはじめて訪れた人のことです。', 'hametuha' ) }
						</div>

						{ !! this.state.new ? (
							<ResponsiveContainer width="100%" height={ 300 }>
								<PieChart width="100%" height={300}>
									<Legend verticalAlign="top" height={36}/>
									<Tooltip />
									<Pie
										data={ this.state.new }
										cx="50%"
										cy="50%"
										innerRadius={60}
										outerRadius={80}
										dataKey="value"
									>
										{ gender.map( ( entry, index ) => (
											<Cell key={`cell-${index}`} fill={chartColors( index ) } unit={ __( '人', 'hametuha' ) } />
										) ) }
									</Pie>
								</PieChart>
							</ResponsiveContainer>
						) : (
							noContent()
						) }
					</div>

					<div className="col-12 col-md-6">
						<h3>{ __( '読者の地域', 'hametuha' ) }</h3>
						<div className="mt-4 mb-4 alert alert-secondary" role="alert">
							{ __( '上位50件を表示しています。', 'hametuha' ) }
						</div>
						{ !! region ? (
							<ResponsiveContainer width="100%" height={ 900 }>
								<BarChart width="100%" minHeight={ 900 } data={ region } layout="vertical">
									<CartesianGrid strokeDasharray="3 3"/>
									<YAxis dataKey="name" type="category" />
									<XAxis dataKey="value" type="number" />
									<Tooltip/>
									<Legend/>
									<Bar layout="vertical" dataKey="value" fill={ chartColors( 0 ) } unit={ __( '人', 'hametuha' ) }/>
								</BarChart>
							</ResponsiveContainer>
						) : (
							noContent()
						) }
					</div>
				</div>

				<LoadingIndicator loading={ loading } />
			</div>
		);
	}
}

if ( div ) {
	if ( createRoot ) {
		createRoot( div ).render( <StatsAudiences target={ target } from={ toDateTime( from ) } to={ toDateTime( to ) } /> );
	} else {
		render( <StatsAudiences target={ target } from={ toDateTime( from ) } to={ toDateTime( to ) } />, div );
	}
}
