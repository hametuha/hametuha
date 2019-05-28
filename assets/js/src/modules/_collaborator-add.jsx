const { Component } = wp.element;


export class CollaboratorAdd extends Component {

  constructor( props ) {
    super( props );
    console.log( props );
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
      <div className={ this.state.loading ? 'hametuha-loading' : ''}>
        <input type='text' className='widefat' placeholder='ユーザーIDからスラッグを入れてください。'
          value={this.state.slug} onChange={ e => this.setState( { slug: e.target.value } ) } />
        <a href='#' className='button' onClick={ e => { e.preventDefault(); this.handleClick() } }>追加</a>
      </div>
    );
  }
}
