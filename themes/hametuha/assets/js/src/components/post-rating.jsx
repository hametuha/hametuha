/*!
 * 投稿を星で評価する
 *
 * @deps wp-element, wp-api-fetch, wp-i18n, hametuha-toast
 * @handle hametuha-components-post-rating
 * @strategy defer
 */

const { createRoot, useState } = wp.element;
const { __ } = wp.i18n;
const apiFetch = wp.apiFetch;
const { toast } = wp.hametuha;

/**
 * 星評価コンポーネント
 *
 * @param {Object} props
 * @param {number} props.postId - 投稿ID
 * @param {number} props.initialRating - 初期評価値（0-5）
 */
const StarRating = ( { postId, initialRating } ) => {
	const [ rating, setRating ] = useState( initialRating || 0 );
	const [ tempRating, setTempRating ] = useState( initialRating || 0 );
	const [ isEditing, setIsEditing ] = useState( false );
	const [ isSaving, setIsSaving ] = useState( false );
	const [ saveTimeout, setSaveTimeout ] = useState( null );

	/**
	 * 評価を保存する
	 *
	 * @param {number} newRating - 新しい評価値（0-5）
	 */
	const saveRating = async ( newRating ) => {
		setIsSaving( true );
		try {
			const response = await apiFetch( {
				path: `/hametuha/v1/feedback/rating/${ postId }`,
				method: 'POST',
				data: {
					rating: newRating,
				},
			} );
			setRating( newRating );
			setIsEditing( false );
			toast( response.message || __( '評価を更新しました。', 'hametuha' ), 'success' );
		} catch ( error ) {
			toast( error.message || __( '評価の更新に失敗しました。', 'hametuha' ), 'error' );
		} finally {
			setIsSaving( false );
		}
	};

	/**
	 * range inputの変更ハンドラー（デバウンス付き）
	 */
	const handleChange = ( event ) => {
		const newRating = parseInt( event.target.value, 10 );
		setTempRating( newRating );

		// 既存のタイマーをクリア
		if ( saveTimeout ) {
			clearTimeout( saveTimeout );
		}

		// 800ms後に保存
		const timeout = setTimeout( () => {
			saveRating( newRating );
		}, 800 );

		setSaveTimeout( timeout );
	};

	/**
	 * 評価テキストを取得
	 */
	const getRatingText = () => {
		return <>
			{ __( 'あなたの評価', 'hametuha' ) }
			<br />
			{ starText() }
		</>
	};

	const starText = () => {
		if ( 1 > tempRating ) {
			return <span className="text-muted">{ __( '未評価', 'hametuha' ) }</span>
		} else {
			const stars = [];
			for ( let i = 0; i < tempRating; i++ ) {
				stars.push( <span className="text-star">★</span> );
			}
			return stars;
		}
	};

	/**
	 * 編集モードのキャンセル
	 */
	const handleCancel = () => {
		if ( saveTimeout ) {
			clearTimeout( saveTimeout );
		}
		setTempRating( rating );
		setIsEditing( false );
	};

	if ( isEditing ) {
		return (
			<div className="post-rating-editor">
				<div className="post-rating-slider">
					<label htmlFor={ `rating-slider-${ postId }` }>
						{ __( '評価を選択:', 'hametuha' ) }
						{ starText() }
					</label>
					<input
						id={ `rating-slider-${ postId }` }
						type="range"
						min="0"
						max="5"
						step="1"
						value={ tempRating }
						onChange={ handleChange }
						disabled={ isSaving }
						className="form-range"
						title={ __( '作品への評価', 'hametuha' ) }
					/>
				</div>
				<button
					type="button"
					className="btn btn-sm btn-secondary mt-2"
					onClick={ handleCancel }
					disabled={ isSaving }
				>
					{ __( '保存中...', 'hametuha' ) }
				</button>
			</div>
		);
	}

	/**
	 * 編集モードを開始
	 */
	const startEditing = () => {
		setTempRating( rating );
		setIsEditing( true );
	};

	return (
		<div className="post-rating-display">
			<p className="mb-2">
				<strong>{ getRatingText() }</strong>
			</p>
			<button
				type="button"
				className="btn btn-sm btn-outline-primary"
				onClick={ startEditing }
			>
				{ rating === 0 ? __( '評価する', 'hametuha' ) : __( '評価を変更', 'hametuha' ) }
			</button>
		</div>
	);
};

// すべてのコンテナを検索してマウント
const containers = document.querySelectorAll( '.hametuha-post-rating' );

containers.forEach( ( container ) => {
	const postId = parseInt( container.dataset.postId, 10 );
	const initialRating = parseInt( container.dataset.rating, 10 ) || 0;

	if ( postId ) {
		createRoot( container ).render(
			<StarRating postId={ postId } initialRating={ initialRating } />
		);
	}
} );
