const { Component } = wp.element;


export class CollaboratorAdd extends Component {

  constructor( props ) {
    super( props );
    this.state = {
      loading: false,
      slug: '',
      margin: 10
    };
  }

  handleClick() {
    this.setState( {
      loading: true,
    }, () => {
      wp.apiFetch({
        path: `hametuha/v1/collaborators/${this.props.postId}`,
        method: 'POST',
        data: {
          collaborator: this.state.slug,
          margin: this.state.margin
        }
      }).then( res => {
        this.props.addHandler( res );
      }).catch( res => {
        alert( res.message || 'ユーザーを追加できませんでした。' );
      }).finally( res => {
        this.setState( {
          loading: false,
          slug: '',
          margin: 10,
        } );
      });
    } );
  }

  render() {
    return (
      <tr className={ this.state.loading ? 'hametuha-loading' : '' }>
        <td colSpan={3}>
          <input type='text' className='widefat' placeholder='ユーザーのスラッグ（URLの名前）を入れてください。'
                 value={ this.state.slug } onChange={ e => this.setState( { slug: e.target.value } ) }/>
        </td>
        <td>
          <input type='number' style={{width: '3em', textAlign: 'right'}}
                 value={ this.state.margin } onChange={ e => this.setState( { margin: e.target.value } ) }
                 min={1} max={100}/>%
        </td>
        <td className='collaborators-actions'>
          <a href='#' className='button' onClick={ e => { e.preventDefault(); this.handleClick() } }>
            <span className='dashicons dashicons-plus-alt' />&nbsp;追加
          </a>
        </td>
      </tr>
    );
  }
}
