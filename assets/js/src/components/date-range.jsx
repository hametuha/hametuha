/*!
 * Date Range Component
 *
 * @handle hametuha-date-range
 * @deps wp-element, wp-i18n, hametuha-components
 */

const { __ } = wp.i18n;
const { Component } = wp.element;

class DateRange extends Component {

	constructor( props ) {
		super( props );
		this.state = {
			from: props.from,
			to: props.to,
		};
	}

	render() {
		const { from, to } = this.state;
		const { onChange } = this.props;
		return (
			<div className="form-row align-items-end">
				<div className="form-group col">
					<label htmlFor="user-pv-from">{ __( '開始', 'hametuha' ) }</label>
					<input id="user-pv-from" className="form-control" type="date" value={ from } onChange={ e => this.setState( { from: e.target.value } ) }/>
				</div>
				<div className="form-group col">
					<label id="user-pv-to">{ __( '終了', 'hametuha' ) }</label>
					<input id="user-pv-to" className="form-control" type="date" value={ to } onChange={ e => this.setState( { to: e.target.value } ) }/>
				</div>
				<div className="form-group col">
					<button className="btn btn-primary" onClick={ () => {
						onChange( from, to );
					} }>
						{ __( '更新', 'hametuha' ) }
					</button>
				</div>
			</div>
		);
	}
}

wp.hametuha.DateRange = DateRange;
