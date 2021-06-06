const { Component } = wp.element;

export class Collaborator extends Component {

  constructor( props ) {
    super( props );
    this.state = {
      ratio: this.props.user.ratio,
    };
    this.handleDelete = this.handleDelete.bind( this );
    this.handleEdit   = this.handleEdit.bind( this );
  }

  handleEdit( e ) {
    e.preventDefault();
    this.props.updateHandler( this.props.user.id, this.state.ratio );
  }

  handleDelete( e ) {
    e.preventDefault();
    if ( confirm( `${this.props.user.name}さんを関係者から削除してよろしいですか？` ) ) {
      this.props.deleteHandler( this.props.user );
    }
  }

  render() {
    let className = ['collaborators-item'];
    const assigned = this.props.user.assigned;
    return (
      <tr className={ className.join( ' ' ) }>
        <th className='collaborators-list-number'>{ this.props.user.id }</th>
        <td>
          <img alt={this.props.user.name} className='collaborators-avatar' src={ this.props.user.avatar } />
          <a className='collaborators-name' href={ this.props.user.url }>{ this.props.user.name }</a>
          <small>（{ this.props.user.label }）</small>
        </td>
        <td className='collaborators-revenue'>
          { this.props.user.ratio < 0 ? (
            <span className='collaborators-revenue-waiting'>
              <span className='dashicons dashicons-warning'/>
              承認待ち
            </span>
          ) : (
            <label>
              <input className='collaborators-revenue-input' type='number'
                     step={1} max={100} min={0} value={this.state.ratio}
                     onChange={ e => this.setState( { ratio: e.target.value } ) }/>
              %
            </label>
          )}
        </td>
        <td className='collaborators-assigned'>
          <span title={assigned} className='dashicons dashicons-clock'/>
          <time dateTime={assigned}>{ moment( assigned ).format('YYYY.MM.DD' ) }</time>
        </td>
        <td className='collaborators-actions'>
          { 0 > this.props.user.ratio ? null : (
            <button className='button' onClick={ this.handleEdit }>更新</button>
          )}
          <a className='collaborators-delete-link' href='#' onClick={ this.handleDelete } >削除</a>
        </td>
      </tr>
    )
  }

}
