/*!
 * 合評会の当日採点を入力する
 *
 * @deps wp-element, wp-api-fetch, wp-i18n, hametuha-toast
 * @handle hametuha-components-joint-review-input
 * @strategy defer
 */

const { createRoot, useState } = wp.element;
const { __, sprintf } = wp.i18n;
const apiFetch = wp.apiFetch;
const { toast } = wp.hametuha;

const EPSILON = 0.001;

/**
 * 合計を小数誤差を吸収して整形する
 *
 * @param {number} value 値
 * @return {string} 表示用文字列
 */
const formatNumber = ( value ) => {
	return parseFloat( value.toFixed( 2 ) ).toString();
};

/**
 * 当日採点入力コンポーネント
 *
 * @param {Object} props
 * @param {number} props.termId       公募タームID
 * @param {number} props.allotment    持ち点
 * @param {Array}  props.works        作品一覧 [{ id, title, own }]
 * @param {Object} props.distribution 現在の配分 { id: point }
 */
const JointReviewInput = ( { termId, allotment, works, distribution } ) => {
	const initial = {};
	works.forEach( ( work ) => {
		initial[ work.id ] = work.own ? 0 : parseFloat( distribution[ work.id ] || 0 );
	} );
	const [ scores, setScores ] = useState( initial );
	const [ isSaving, setIsSaving ] = useState( false );

	const sum = works.reduce( ( acc, work ) => acc + ( parseFloat( scores[ work.id ] ) || 0 ), 0 );
	const remaining = allotment - sum;
	const canSubmit = Math.abs( remaining ) <= EPSILON && ! isSaving;

	/**
	 * 入力値の変更
	 *
	 * @param {number} workId 作品ID
	 * @param {string} value  入力値
	 */
	const handleChange = ( workId, value ) => {
		const point = value === '' ? 0 : parseFloat( value );
		setScores( {
			...scores,
			[ workId ]: isNaN( point ) || point < 0 ? 0 : point,
		} );
	};

	/**
	 * 採点を送信する
	 */
	const handleSubmit = async () => {
		setIsSaving( true );
		try {
			const response = await apiFetch( {
				path: `/hametuha/v1/campaign/joint-review/${ termId }`,
				method: 'POST',
				data: { scores },
			} );
			toast( response.message || __( '採点を保存しました。', 'hametuha' ), 'success' );
		} catch ( error ) {
			toast( error.message || __( '採点の保存に失敗しました。', 'hametuha' ), 'error' );
		} finally {
			setIsSaving( false );
		}
	};

	const remainingClass = Math.abs( remaining ) <= EPSILON ? 'text-success' : 'text-danger';

	return (
		<div className="joint-review-input">
			<table className="table joint-review-input__table">
				<tbody>
					{ works.map( ( work ) => (
						<tr key={ work.id }>
							<th className="joint-review-input__work">{ work.title }</th>
							<td className="joint-review-input__field">
								{ work.own ? (
									<span className="text-muted">{ __( '自作品（採点不可）', 'hametuha' ) }</span>
								) : (
									<input
										type="number"
										min="0"
										step="0.1"
										value={ scores[ work.id ] }
										onChange={ ( event ) => handleChange( work.id, event.target.value ) }
										disabled={ isSaving }
										className="form-control"
										title={ work.title }
									/>
								) }
							</td>
						</tr>
					) ) }
				</tbody>
			</table>
			<p className="joint-review-input__remaining">
				{ sprintf(
					/* translators: %1$s: remaining, %2$s: allotment */
					__( '残り持ち点：%1$s / %2$s', 'hametuha' ),
					formatNumber( remaining ),
					formatNumber( allotment )
				) }
				<span className={ remainingClass }>
					{ ' ' }
					{ Math.abs( remaining ) <= EPSILON
						? __( '（OK）', 'hametuha' )
						: __( '（ちょうど使い切ってください）', 'hametuha' ) }
				</span>
			</p>
			<button
				type="button"
				className="btn btn-primary"
				onClick={ handleSubmit }
				disabled={ ! canSubmit }
			>
				{ isSaving ? __( '保存中…', 'hametuha' ) : __( '採点を保存する', 'hametuha' ) }
			</button>
		</div>
	);
};

// すべてのコンテナを検索してマウントする。
document.querySelectorAll( '.hametuha-joint-review-input' ).forEach( ( container ) => {
	const termId = parseInt( container.dataset.termId, 10 );
	const allotment = parseFloat( container.dataset.allotment ) || 0;
	let works = [];
	let distribution = {};
	try {
		works = JSON.parse( container.dataset.works || '[]' );
		distribution = JSON.parse( container.dataset.distribution || '{}' );
	} catch ( e ) {
		works = [];
	}

	if ( termId && works.length ) {
		createRoot( container ).render(
			<JointReviewInput
				termId={ termId }
				allotment={ allotment }
				works={ works }
				distribution={ distribution }
			/>
		);
	}
} );
