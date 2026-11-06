/*!
 * Tag Selector Component
 *
 * 投稿エディターのタグ選択UIをトークン式 + モーダル選択に改善
 *
 * @handle hametuha-tag-selector
 * @deps wp-element, wp-components, wp-i18n
 */

const { render, useState, useCallback, useMemo, useEffect } = wp.element;
const { FormTokenField, Button, Modal, SearchControl, PanelBody, CheckboxControl } = wp.components;
const { __ } = wp.i18n;

/**
 * タグ選択モーダルコンポーネント
 */
const TagSelectorModal = ( { isOpen, onClose, tagsByGenre, selectedTags, onTagsChange } ) => {
	const [ searchQuery, setSearchQuery ] = useState( '' );
	const [ localSelectedTags, setLocalSelectedTags ] = useState( selectedTags );

	// 検索フィルタリング
	const filteredTagsByGenre = useMemo( () => {
		if ( ! searchQuery ) {
			return tagsByGenre;
		}
		const query = searchQuery.toLowerCase();
		const filtered = {};
		Object.keys( tagsByGenre ).forEach( ( genre ) => {
			const filteredTags = tagsByGenre[ genre ].filter( ( tag ) =>
				tag.name.toLowerCase().includes( query )
			);
			if ( filteredTags.length > 0 ) {
				filtered[ genre ] = filteredTags;
			}
		} );
		return filtered;
	}, [ tagsByGenre, searchQuery ] );

	// タグの選択/解除
	const toggleTag = useCallback( ( tagName ) => {
		setLocalSelectedTags( ( prev ) => {
			if ( prev.includes( tagName ) ) {
				return prev.filter( ( t ) => t !== tagName );
			}
			return [ ...prev, tagName ];
		} );
	}, [] );

	// 完了ボタン
	const handleComplete = useCallback( () => {
		onTagsChange( localSelectedTags );
		onClose();
	}, [ localSelectedTags, onTagsChange, onClose ] );

	// モーダルが開くたびにローカル状態を同期
	useEffect( () => {
		if ( isOpen ) {
			setLocalSelectedTags( selectedTags );
		}
	}, [ selectedTags, isOpen ] );

	if ( ! isOpen ) {
		return null;
	}

	return (
		<Modal
			title={ __( 'タグを選択', 'hametuha' ) }
			onRequestClose={ onClose }
			className="hametuha-tag-selector-modal"
		>
			<p className="hametuha-tag-selector-modal__description">
				{ __( 'ジャンル別に整理されたタグから選択できます', 'hametuha' ) }
			</p>

			<div className="hametuha-tag-selector-modal__content">
				{/* 左側: 選択中のタグ */}
				<div className="hametuha-tag-selector-modal__selected">
					<h4>
						{ __( '選択中のタグ', 'hametuha' ) }
						<span className="hametuha-tag-selector-modal__count">
							({ localSelectedTags.length })
						</span>
					</h4>
					<div className="hametuha-tag-selector-modal__tokens">
						{ localSelectedTags.length === 0 ? (
							<p className="description">{ __( 'タグが選択されていません', 'hametuha' ) }</p>
						) : (
							localSelectedTags.map( ( tag ) => (
								<span key={ tag } className="hametuha-tag-selector-modal__token">
									{ tag }
									<button
										type="button"
										className="hametuha-tag-selector-modal__token-remove"
										onClick={ () => toggleTag( tag ) }
										aria-label={ __( '削除', 'hametuha' ) }
									>
										&times;
									</button>
								</span>
							) )
						) }
					</div>
				</div>

				{/* 右側: 利用可能なタグ */}
				<div className="hametuha-tag-selector-modal__available">
					<h4>{ __( '利用可能なタグ', 'hametuha' ) }</h4>
					<SearchControl
						value={ searchQuery }
						onChange={ setSearchQuery }
						placeholder={ __( 'タグを検索...', 'hametuha' ) }
					/>
					<div className="hametuha-tag-selector-modal__genres">
						{ Object.keys( filteredTagsByGenre ).map( ( genre ) => (
							<PanelBody
								key={ genre }
								title={
									<span>
										{ genre }
										<span className="hametuha-tag-selector-modal__genre-count">
											{ filteredTagsByGenre[ genre ].length }
										</span>
									</span>
								}
								initialOpen={ false }
							>
								<div className="hametuha-tag-selector-modal__tag-list">
									{ filteredTagsByGenre[ genre ].map( ( tag ) => (
										<div
											key={ tag.term_id }
											className={ `hametuha-tag-selector-modal__tag-item ${
												localSelectedTags.includes( tag.name ) ? 'is-selected' : ''
											}` }
											onClick={ () => toggleTag( tag.name ) }
										>
											<span className="hametuha-tag-selector-modal__tag-name">
												{ tag.name }
											</span>
											{ localSelectedTags.includes( tag.name ) && (
												<span className="hametuha-tag-selector-modal__check">&#10003;</span>
											) }
										</div>
									) ) }
								</div>
							</PanelBody>
						) ) }
					</div>
				</div>
			</div>

			{/* フッター */}
			<div className="hametuha-tag-selector-modal__footer">
				<span>
					{ localSelectedTags.length }{ __( '個のタグを選択中', 'hametuha' ) }
				</span>
				<Button variant="primary" onClick={ handleComplete }>
					{ __( '完了', 'hametuha' ) }
				</Button>
			</div>
		</Modal>
	);
};

/**
 * メインのタグセレクターコンポーネント
 */
const TagSelector = ( { tagsByGenre, initialTags, hiddenInputId } ) => {
	const [ selectedTags, setSelectedTags ] = useState( initialTags );
	const [ isModalOpen, setIsModalOpen ] = useState( false );

	// すべてのタグ名のリスト（サジェスト用）
	const allTagNames = useMemo( () => {
		const names = [];
		Object.values( tagsByGenre ).forEach( ( tags ) => {
			tags.forEach( ( tag ) => {
				names.push( tag.name );
			} );
		} );
		return names;
	}, [ tagsByGenre ] );

	// hidden inputの更新
	const updateHiddenInput = useCallback( ( tags ) => {
		const hiddenInput = document.getElementById( hiddenInputId );
		if ( hiddenInput ) {
			hiddenInput.value = tags.join( ', ' );
		}
	}, [ hiddenInputId ] );

	// タグ変更時
	const handleTagsChange = useCallback( ( newTags ) => {
		// 日本語読点をカンマに変換
		const normalizedTags = newTags.map( ( tag ) =>
			typeof tag === 'string' ? tag.replace( /、/g, ',' ).trim() : tag
		);
		// カンマで分割されたタグを展開
		const expandedTags = [];
		normalizedTags.forEach( ( tag ) => {
			if ( typeof tag === 'string' && tag.includes( ',' ) ) {
				tag.split( ',' ).forEach( ( t ) => {
					const trimmed = t.trim();
					if ( trimmed && ! expandedTags.includes( trimmed ) ) {
						expandedTags.push( trimmed );
					}
				} );
			} else if ( tag && ! expandedTags.includes( tag ) ) {
				expandedTags.push( tag );
			}
		} );
		setSelectedTags( expandedTags );
		updateHiddenInput( expandedTags );
	}, [ updateHiddenInput ] );

	return (
		<div className="hametuha-tag-selector">
			<FormTokenField
				value={ selectedTags }
				suggestions={ allTagNames }
				onChange={ handleTagsChange }
				placeholder={ __( 'タグを入力してEnter（カンマ区切り可）', 'hametuha' ) }
				__experimentalExpandOnFocus={ true }
				__experimentalAutoSelectFirstMatch={ true }
			/>

			<Button
				variant="secondary"
				className="hametuha-tag-selector__modal-button"
				onClick={ () => setIsModalOpen( true ) }
			>
				<span className="dashicons dashicons-external" />
				{ __( 'タグ一覧から選択', 'hametuha' ) }
			</Button>

			<p className="description hametuha-tag-selector__hint">
				{ __( 'ヒント: カンマ区切りで複数入力可。詳細モードでジャンル別に選択できます。', 'hametuha' ) }
			</p>

			<TagSelectorModal
				isOpen={ isModalOpen }
				onClose={ () => setIsModalOpen( false ) }
				tagsByGenre={ tagsByGenre }
				selectedTags={ selectedTags }
				onTagsChange={ handleTagsChange }
			/>
		</div>
	);
};

// DOMにマウント
const container = document.getElementById( 'hametuha-tag-selector-root' );

if ( container && window.HametuhaTagData ) {
	const { tagsByGenre, selectedTags, hiddenInputId } = window.HametuhaTagData;
	render(
		<TagSelector
			tagsByGenre={ tagsByGenre }
			initialTags={ selectedTags }
			hiddenInputId={ hiddenInputId }
		/>,
		container
	);
}
