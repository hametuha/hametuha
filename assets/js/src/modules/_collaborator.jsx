const { Component } = wp.element;

export class Collaborator extends Component {

  constructor( props ) {
    super( props );
    this.state = {
      ratio: this.props.user.ratio * 100,
    };
  }

  render() {
    let className = ['collaborators-item'];
    const assigned = this.props.user.assigned;
    return (
      <li className={ className.join( ' ' ) }>
        <img alt={this.props.user.name} className='collaborators-avatar' src={ this.props.user.avatar } />
        <a className='collaborators-name' href={ this.props.user.url }>{ this.props.user.name }</a>
        { this.props.user.ratio < 0 ? (
          <span className='collaborators-revenue-waiting'>
            <span className='dashicons dashicons-warning' />
            承認待ち
          </span>
        ) : (
          <input className='collaborators-revenue' type='number' step={1} max={100} min={0} value={ this.state.ratio } onChange={ e => { this.setState( { ratio: e.target.value } ) }} />
        ) }
        <span className='collaborators-assigned'>
          <span title={ assigned } className='dashicons dashicons-clock' />
          <time dateTime={ assigned }>{ moment( assigned ).format( 'YYYY/MM/DD' ) }</time>
        </span>
        <a className='collaborators-delete-link' href='#' onClick={ e => {
          e.preventDefault();
          confirm( `${ this.props.user.name }さんを関係者から削除してよろしいですか？` ) && this.props.deleteHandler( this.props.user );
        } } >
          削除
        </a>
      </li>
    )
  }

}
