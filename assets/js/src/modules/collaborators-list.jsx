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
    this.addHandler    = this.addHandler.bind( this );
    this.updateHandler = this.updateHandler.bind( this );
    this.deleteHandler = this.deleteHandler.bind( this );
  }

  componentDidMount() {
    wp.apiFetch( {
      path: `/hametuha/v1/collaborators/${CollaboratorsList.series_id}`
    } ).then( res => {
      this.setState( {
        collaborators: res,
      });
    } ).catch( res => {
      alert( res.message || '関係者一覧を取得できませんでした。' );
    } ).finally( res => {
      this.setState( {
        loading: false,
      } );
    } );
  }

  addHandler( user ) {
    const collaborators = [];
    for ( const collaborator of this.state.collaborators ) {
      collaborators.push( collaborator );
    }
    collaborators.push( user );
    this.setState( { collaborators } );
  }

  updateHandler( userId, ratio ) {
    this.setState( { loading: true }, () => {
      wp.apiFetch( {
        path: `/hametuha/v1/collaborators/${CollaboratorsList.series_id}`,
        method: 'PUT',
        data: {
          collaborator_id: userId,
          margin: ratio
        }
      } ).then( res => {
        const collaborators = [];
        for ( let collaborator of this.state.collaborators ) {
          if ( collaborator.id === userId ) {
            collaborator.ratio = ratio;
          }
          collaborators.push( collaborator );
        }
        this.setState( {
          collaborators
        } );
      } ).catch( res => {
        alert( res.message || '更新できませんでした。' );
      } ).finally( res => {
        this.setState( { loading: false } )
      } );
    });
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

  render() {
    const containerClassName = [ 'collaborators-list' ];
    const styles = {};
    if ( this.state.loading ) {
      containerClassName.push( 'hametuha-loading' );
      styles.minHeight = '150px';
    }
    return (
      <Fragment>
        <table className={containerClassName.join(' ')} style={styles}>
          <caption>関係者</caption>
          <thead>
            <tr>
              <th className='collaborators-list-number'>#</th>
              <th style={ { textAlign: 'left' } }>名前</th>
              <th style={ { textAlign: 'left' } }>報酬</th>
              <th className='collaborators-assigned'>追加日時</th>
              <th>アクション</th>
            </tr>
          </thead>
          <tfoot>
            <CollaboratorAdd postId={ this.state.id } addHandler={ this.addHandler }/>
          </tfoot>
          <tbody>
          { this.state.collaborators.length ? this.state.collaborators.map( user => {
            return <Collaborator key={ user.id } user={ user } deleteHandler={ this.deleteHandler } updateHandler={ this.updateHandler }/>
          }) : (
            <tr>
              <td colSpan={ 5 }>
                <p className='description' style={ { textAlign: 'center' } }>収入をシェアする人は登録されていません。</p>
              </td>
            </tr>
          ) }
          </tbody>
        </table>
      </Fragment>
    )
  }

}

const container = document.getElementById( 'series-collaborators' );
if ( container ) {
  render( <CollaboratorContainer />, container );
}
