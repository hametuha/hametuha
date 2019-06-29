const { Component } = wp.element;


export class CollaboratorAdd extends Component {

  constructor( props ) {
    super( props );
    this.state = {
      loading: false,
      slug: '',
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
        }
      }).then( res => {
        this.props.addHandler( res );
      }).catch( res => {
        alert( res.message || 'ユーザーを追加できませんでした。' );
      }).finally( res => {
        this.setState( { loading: false } );
      });
    } );
  }

  render() {
    return (
      <tr className={ this.state.loading ? 'hametuha-loading' : '' }>
        <td>&nbsp;</td>
        <td colSpan={3}>
          <input type='text' className='widefat' placeholder='ユーザーIDからスラッグを入れてください。'
                 value={ this.state.slug } onChange={ e => this.setState( { slug: e.target.value } ) }/>
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
