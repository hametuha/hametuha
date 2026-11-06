/*!
 * Hametuha Dashboard - Traffic stats
 *
 * @handle hametuha-hb-stats-traffic
 * @deps wp-element, hametuha-date-range, hametuha-toast, wp-i18n, hametuha-loading-indicator, wp-api-fetch, wp-url, recharts, hametuha-chart-colors
 */

const { render, createRoot, Component } = wp.element;
const { __ } = wp.i18n;
const { apiFetch } = wp;
const { addQueryArgs } = wp.url;
const { DateRange, toast, LoadingIndicator, toDateTime, chartColors } = wp.hametuha;
const { PieChart, Pie, Cell, Bar, Line, LineChart, ResponsiveContainer, Legend, Tooltip, CartesianGrid, XAxis, YAxis} = Recharts;

// Get div elements.
const div = document.getElementById( 'hametuha-user-analytics' );
const target = div?.dataset?.target || '';
// Set default dates.
const to = new Date();
const from = new Date();
from.setDate( from.getDate() - 30 );


class Stats extends Component {

	constructor( props ) {
		super( props );
		this.state = {
			from: props.from,
			to: props.to,
			loading: false,
			source: [],
			contributors: [],
			profiles: [],
		};
	}

	componentDidMount() {
		this.fetch();
	}

	fetch() {


		this.setState( { loading: true }, () => {
			apiFetch( {
				path: addQueryArgs( '/hametuha/v1/stats/traffic/me/', {
					from: this.state.from,
					to: this.state.to,
				} ),
			} ).then( res => {
				const newState = {};
				for ( const key of [ 'source', 'contributors', 'profiles' ] ) {
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
			case 'source':
				labels = {
					'(direct)': __( '直接', 'hametuha' ),
					'': __( '不明', 'hametuha' ),
				};
				for ( const item of data ) {
					const value = parseInt( item[2] );
					if ( 5 > value) {
						continue;
					}
					ret.push( {
						name: labels[ item[0] ] || item[0],
						value,
					} );
				}
				break;
			case 'profiles':
				for ( const item of data ) {
					const value = parseInt( item[ item.length - 1 ] );
					ret.push( {
						name: item[0],
						value,
					} );
				}
				break;
			default:
				for ( const item of data ) {
					const value = parseInt( item[ item.length - 1 ] );
					if ( 2 > value) {
						continue;
					}
					ret.push( {
						name: item[0],
						value,
					} );
				}
				break;
		}
		return ret;
	}

	render() {

		const { loading, contributors, profiles, source } = this.state;

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
						<h3>{ __( 'アクセス元', 'hametuha' ) }</h3>
						<div className="mt-4 mb-4 alert alert-secondary" role="alert">
							{ __( 'アクセス元別の訪問数です。検索が多い場合は作品に対する検索需要が、SNSが多い場合は注目度が高いです。', 'hametuha' ) }
						</div>

						{ !! source.length ? (
							<ResponsiveContainer width="100%" height={ 300 }>
								<PieChart width="100%" height={300}>
									<Legend verticalAlign="top" height={36}/>
									<Tooltip />
									<Pie
										data={ source }
										cx="50%"
										cy="50%"
										innerRadius={60}
										outerRadius={80}
										dataKey="value"
									>
										{ source.map( ( entry, index ) => (
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
						<h3>{ __( '貢献した人', 'hametuha' ) }</h3>
						<div className="mt-4 mb-4 alert alert-secondary" role="alert">
							{ __( '破滅派のユーザーがSNSなどでシェアした場合、こちらに記録されます。破滅派自動が下がるようにするとよいでしょう。', 'hametuha' ) }
						</div>
						{ !! contributors.length ? (
							<ResponsiveContainer width="100%" height={ 300 }>
								<PieChart width="100%" height={300}>
									<Legend verticalAlign="top" height={36}/>
									<Tooltip />
									<Pie
										data={ contributors }
										cx="50%"
										cy="50%"
										innerRadius={60}
										outerRadius={80}
										dataKey="value"
									>
										{ contributors.map( ( entry, index ) => (
											<Cell key={`cell-${index}`} fill={chartColors( index ) } unit={ __( '人', 'hametuha' ) } />
										) ) }
									</Pie>
								</PieChart>
							</ResponsiveContainer>
						) : (
							noContent()
						) }
					</div>
				</div>

				<hr />

				<div className="row">

					<div className="col-12">
						<h3>{ __( 'プロフィールページ閲覧数', 'hametuha' ) }</h3>
						<div className="mt-4 mb-4 alert alert-secondary" role="alert">
							{ __( 'あなたのプロフィールに興味を持った人の数です。この数が増えるように頑張りましょう。', 'hametuha' ) }
						</div>
						{ !! profiles.length ? (
							<ResponsiveContainer width="100%" height={ 300 }>
								<LineChart width={730} height={250} data={ profiles }
										   margin={{ top: 5, right: 30, left: 20, bottom: 5 }}>
									<CartesianGrid strokeDasharray="3 3" />
									<XAxis dataKey="name" />
									<YAxis />
									<Tooltip />
									<Legend />
									<Line type="monotone" dataKey="value" stroke={ chartColors( 0 ) } />
								</LineChart>
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
		createRoot( div ).render( <Stats target={ target } from={ toDateTime( from ) } to={ toDateTime( to ) } /> );
	} else {
		render( <Stats target={ target } from={ toDateTime( from ) } to={ toDateTime( to ) } />, div );
	}
}
