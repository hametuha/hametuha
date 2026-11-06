/*!
 * Collaborators List
 *
 * @handle hametuha-module-collaborators-list
 * @deps wp-api-fetch, wp-element, moment
 */

import CollaboratorAdd from "./_collaborator-add.jsx";
import Collaborator from "./_collaborator.jsx";

const { createRoot, render, useState, useEffect } = wp.element;


/* global CollaboratorsList:false */

const CollaboratorContainer = ( props ) => {

	const [ loading, setLoading ] = useState( true );
	const [ collaborators, setCollaborators ] = useState( [] );
	const { postId } = props;
	const { shareType } = CollaboratorsList;
	useEffect( () => {
		wp.apiFetch( {
			path: `/hametuha/v1/collaborators/${ postId }`
		} ).then( res => {
			setCollaborators( res );
		} ).catch( res => {
			alert( res.message || '関係者一覧を取得できませんでした。' );
		} ).finally( res => {
			setLoading( false );
		} );
	}, [] );

	const addHandler = ( user ) => {
		const newCollaborators = [];
		for ( const collaborator of collaborators ) {
			newCollaborators.push( collaborator );
		}
		newCollaborators.push( user );
		setCollaborators( newCollaborators );
	}

	const updateHandler = ( userId, ratio ) => {
		setLoading( true );
		wp.apiFetch( {
			path: `/hametuha/v1/collaborators/${ postId }`,
			method: 'PUT',
			data: {
				collaborator_id: userId,
				margin: ratio
			}
		} ).then( res => {
			const newCollaborators = [];
			for ( let collaborator of collaborators ) {
				if ( collaborator.id === userId ) {
					collaborator.ratio = ratio;
				}
				newCollaborators.push( collaborator );
			}
			setCollaborators( newCollaborators );
		} ).catch( res => {
			alert( res.message || '更新できませんでした。' );
		} ).finally( res => {
			setLoading( false )
		} );
	}

	const deleteHandler = ( user ) => {
		setLoading( true );
		wp.apiFetch( {
			path: `/hametuha/v1/collaborators/${ postId }?collaborator_id=${ user.id }`,
			method: 'DELETE'
		} ).then( res => {
			const newCollaborators = [];
			for ( let collaborator of collaborators ) {
				if ( collaborator.id !== res.id ) {
					newCollaborators.push( collaborator );
				}
			}
			setCollaborators( newCollaborators );
		} ).catch( res => {
			alert( res.message || '削除に失敗しました。' );
		} ).finally( res => {
			setLoading( false )
		} );
	}

	const containerClassName = [ 'collaborators-list' ];
	const styles = {};
	if ( loading ) {
		containerClassName.push( 'hametuha-loading' );
		styles.minHeight = '150px';
	}
	return (
		<table className={ containerClassName.join( ' ' ) } style={ styles }>
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
			<CollaboratorAdd postId={ postId } addHandler={ addHandler }/>
			</tfoot>
			<tbody>
			{ collaborators.length ? collaborators.map( user => {
				return <Collaborator
					key={ user.id } user={ user }
					deleteHandler={ deleteHandler }
					updateHandler={ updateHandler }
				/>
			} ) : (
				<tr>
					<td colSpan={ 5 }>
						<p className='description'
						   style={ { textAlign: 'center' } }>収入をシェアする人は登録されていません。</p>
					</td>
				</tr>
			) }
			</tbody>
		</table>
	);

};

// Render container.
const container = document.getElementById( 'series-collaborators' );
if ( container ) {
	const postId = container.dataset.postId;
	if ( createRoot ) {
		createRoot( container ).render( <CollaboratorContainer postId={ postId }/> );
	} else {
		render( <CollaboratorContainer postId={ postId }/>, container );
	}
}
