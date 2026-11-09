/**
 * 協力者の役割を選ぶセレクトボックス
 *
 * 選択肢は PHP（SeriesCollaborators）が CollaboratorsList.collaborator に出している。
 */

/* global CollaboratorsList:false */

export default function CollaboratorType( { value, onChange } ) {
	const types = CollaboratorsList.collaborator || [];
	return (
		<select
			aria-label="役割"
			value={ value }
			onChange={ ( e ) => onChange( e.target.value ) }
		>
			{ types.map( ( type ) => (
				<option key={ type.key } value={ type.key }>
					{ type.label }
				</option>
			) ) }
		</select>
	);
}
