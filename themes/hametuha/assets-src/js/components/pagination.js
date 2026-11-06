/*!
 * Pagination components
 *
 * @handle hametuha-pagination
 * @deps wp-element, wp-i18n, hametuha-components
 */

const { __ } = wp.i18n;
const { classNames } = wp.hametuha;

const PageItem = ( props ) => {
	const { label, number, disabled, onChange, active } = props;
	const classes = classNames( {
		'page-item': true,
		active,
		disabled,
	} );
	const Icon = ( p ) => {
		switch ( p.label ) {
			case 'left':
			case 'right':
				return <i className="material-icons">{ p.label === 'left' ? 'chevron_left' : 'chevron_right' }</i>;
			case 'more_horiz':
				return <i className="material-icons">more_horiz</i>;
			default:
				return <>{ p.number }</>
		}
	};
	return (
		<li className={ classes }>
			<a className="page-link" href="#" onClick={ e => {
				e.preventDefault();
				if ( disabled || active ) {
					// Do nothing.
					return false;
				}
				onChange( number );
			} }>
				<Icon number={ number } label={ label } />
			</a>
		</li>
	);
};

wp.hametuha.Pagination = ( props ) => {
	const { onChange, current, total } = props;
	if ( ! total ) {
		return null;
	}

	const computed = {
		max: 7,
		hasPrev: function() {
			return 1 < this.leftPad();
		},
		hasNext: function() {
			return this.rightPad() < total;
		},
		pad: function() {
			return Math.floor( ( this.max - 1 ) / 2 );
		},
		leftPad: function() {
			return Math.max( current - this.pad(), 1 );
		},
		rightPad: function() {
			return Math.min( current + this.pad(), total );
		},
		needLeft: function() {
			return 2 < this.leftPad();
		},
		needRight: function() {
			return this.rightPad() < ( total - 1 );
		},
		range: function() {
			const range = [];
			for ( let i = this.leftPad(), l = this.rightPad(); i <= l; i++ ) {
				range.push( i );
			}
			return range;
		},
	};

	return (
		<nav aria-label={ __( 'ページナビゲーション', 'hametuha' ) }>
			<ul className="pagination justify-content-center">
				<PageItem disabled={ ! computed.hasPrev() } label="left" number={ 1 } onChange={ onChange } />
				{ computed.needLeft() && <PageItem label="more_horiz" disabled={ true } onChange={ onChange } /> }
				{ computed.range().map( num => {
					return <PageItem number={ num } active={ num === current } onChange={ onChange }/>
				} ) }
				{ computed.needRight() && <PageItem label="more_horiz" disabled={ true } onChange={ onChange } /> }
				<PageItem disabled={ ! computed.hasNext() } label="right" onChange={ onChange } number={ total } />
			</ul>
		</nav>

	);
};
