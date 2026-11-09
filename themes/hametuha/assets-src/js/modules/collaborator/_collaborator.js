/**
 * コラボレーターを表示する
 *
 */
import CollaboratorType from './_collaborator-type';

const { useState } = wp.element;

export default function Collaborator( props ) {
	const { user } = props;
	const [ ratio, setRatio ] = useState( user.ratio );
	const [ type, setType ] = useState( user.type || 'writer' );
	const className = [ 'collaborators-item' ];
	const assigned = user.assigned;
	return (
		<tr className={ className.join( ' ' ) }>
			<th className="collaborators-list-number">{ user.id }</th>
			<td>
				<img
					alt={ user.name }
					className="collaborators-avatar"
					src={ user.avatar }
				/>
				<a className="collaborators-name" href={ user.url }>
					{ user.name }
				</a>
				&nbsp;
				<CollaboratorType value={ type } onChange={ setType } />
			</td>
			<td className="collaborators-revenue">
				{ ratio < 0 ? (
					<span className="collaborators-revenue-waiting">
						<span className="dashicons dashicons-warning" />
						承認待ち
					</span>
				) : (
					<label>
						<input
							className="collaborators-revenue-input"
							type="number"
							style={ { width: '4em', textAlign: 'right' } }
							step={ 1 }
							max={ 100 }
							min={ 0 }
							value={ ratio }
							onChange={ ( e ) =>
								setRatio( parseInt( e.target.value ) )
							}
						/>
						%
					</label>
				) }
			</td>
			<td className="collaborators-assigned">
				<span
					title={ assigned }
					className="dashicons dashicons-clock"
				/>
				<time dateTime={ assigned }>
					{ moment( assigned ).format( 'YYYY.MM.DD' ) }
				</time>
			</td>
			<td className="collaborators-actions">
				<button
					className="button"
					style={ { marginRight: '0.5em' } }
					onClick={ ( e ) => {
						e.preventDefault();
						// 承認待ちは報酬を変えられないので、招待したときの報酬をそのまま送る。
						props.updateHandler( user.id, Math.abs( ratio ), type );
					} }
				>
					更新
				</button>
				<button
					className="button collaborators-delete-link"
					onClick={ ( e ) => {
						e.preventDefault();
						if (
							confirm(
								`${ user.name }さんを協力者から削除してよろしいですか？`
							)
						) {
							props.deleteHandler( user );
						}
					} }
				>
					削除
				</button>
			</td>
		</tr>
	);
}
