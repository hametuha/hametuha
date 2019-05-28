/*!
 * wpdeps=wp-api-fetch, wp-element, moment
 */

import { CollaboratorAdd } from "./_collaborator-add.jsx";
import { Collaborator } from "./_collaborator.jsx";

const { render, Component, Fragment } = wp.element;


/* global CollaboratorsList:false */

class CollaboratorContainer extends Component {

  constructor( props ) {
    super( props );
    this.state = {
      loading: true,
      collaborators: [],
      shareType: CollaboratorsList.shareType,
      id: parseInt( CollaboratorsList.series_id, 10 ),
    };
    this.deleteHandler = this.deleteHandler.bind( this );
    this.addHandler = this.addHandler.bind( this );
  }

  componentDidMount() {
    wp.apiFetch( {
      path: `/hametuha/v1/collaborators/${CollaboratorsList.series_id}`
    } ).then( res => {
      this.setState( {
        collaborators: res,
      } );
    } ).catch( res => {
      alert( res.message || '関係者一覧を取得できませんでした。' );
    } ).finally( res => {
      this.setState( {
        loading: false,
      } );
    } );
  }

  deleteHandler( user ) {
    this.setState( {
      loading: true
    }, () => {
      wp.apiFetch( {
        path: `/hametuha/v1/collaborators/${CollaboratorsList.series_id}?collaborator_id=${user.id}`,
        method: 'DELETE'
      } ).then( res => {
        const collaborators = [];
        for ( let collaborator of this.state.collaborators ) {
          if ( collaborator.id != res.id ) {
            collaborators.push( collaborator );
          }
        }
        this.setState( {
          collaborators
        } );
      } ).catch( res => {
        alert( res.message || '削除に失敗しました。' );
      } ).finally( res => {
        this.setState( { loading: false } )
      } );
    } );
  }

  addHandler( user ) {
    const collaborators = this.state.collaborators;
    collaborators.push( user );
    this.setState( { collaborators } );
  }

  render() {
    const containerClassName = [ 'collaborators-list' ];
    const styles = {};
    if ( this.state.loading ) {
      containerClassName.push( 'hametuha-loading' );
      styles.minHeight = '150px';
    } else if ( ! this.state.collaborators.length ) {
      styles.display = 'none';
    }
    return (
      <Fragment>
        <hr />
        <h3>関係者</h3>
        <ol className={containerClassName.join(' ')} style={styles}>
          { this.state.collaborators.map(user => {
            return <Collaborator key={ user.id } user={user} deleteHandler={ this.deleteHandler } />
          }) }
        </ol>
        <CollaboratorAdd postId={ this.state.id } addHandler={ this.addHandler }/>
      </Fragment>
    )
  }

}

const container = document.getElementById( 'series-collaborators' );
if ( container ) {
  render( <CollaboratorContainer />, container );
}
