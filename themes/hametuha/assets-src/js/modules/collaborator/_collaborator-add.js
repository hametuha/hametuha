/**
 * Add collaborator
 *
 * @todo ユーザーの検索UIを追加する
 */

import CollaboratorType from './_collaborator-type';

const { useState } = wp.element;

export default function CollaboratorAdd( props ) {
	const [ loading, setLoading ] = useState( false );
	const [ slug, setSlug ] = useState( '' );
	const [ margin, setMargin ] = useState( 10 );
	const [ type, setType ] = useState( 'writer' );

	const handleClick = () => {
		setLoading( true );

		wp.apiFetch( {
			path: `hametuha/v1/collaborators/${ props.postId }`,
			method: 'POST',
			data: {
				collaborator: slug,
				margin,
				type,
			},
		} )
			.then( ( res ) => {
				props.addHandler( res );
			} )
			.catch( ( res ) => {
				alert( res.message || '協力者を追加できませんでした。' );
			} )
			.finally( ( res ) => {
				setLoading( false );
				setSlug( '' );
				setMargin( 10 );
				setType( 'writer' );
			} );
	};

	return (
		<tr className={ loading ? 'hametuha-loading' : '' }>
			<td colSpan={ 3 }>
				<div style={ { display: 'flex', gap: '0.5em' } }>
					<input
						type="text"
						className="widefat"
						placeholder="協力者のスラッグ（URLの名前部分）を入れてください。"
						value={ slug }
						onChange={ ( e ) => setSlug( e.target.value ) }
					/>
					<CollaboratorType value={ type } onChange={ setType } />
				</div>
			</td>
			<td>
				<input
					type="number"
					style={ { width: '4em', textAlign: 'right' } }
					value={ margin }
					onChange={ ( e ) =>
						setMargin( parseInt( e.target.value ) )
					}
					min={ 1 }
					max={ 100 }
				/>
				%
			</td>
			<td className="collaborators-actions">
				<a
					href="#"
					className="button"
					onClick={ ( e ) => {
						e.preventDefault();
						handleClick();
					} }
				>
					<span className="dashicons dashicons-plus-alt" />
					&nbsp;追加
				</a>
			</td>
		</tr>
	);
}
