/*!
 * アイデア投稿用のスクリプト
 *
 * @handle hametuha-components-ideas
 * @deps wp-element, wp-i18n
 */

/* global HametuhaIdeaTags:false */

const { useState, useRef, useEffect, createRoot } = wp.element;
const { __ } = wp.i18n;

const IdeasComponent = () => {
	const [ isModalOpen, setIsModalOpen ] = useState( false );
	const [ isLoading, setIsLoading ] = useState( false );
	const [ message, setMessage ] = useState( '' );
	const [ messageType, setMessageType ] = useState( '' );
	const [ formData, setFormData ] = useState( {
		title: '',
		content: '',
		status: 'publish',
		genre: ''
	} );

	const modalRef = useRef( null );
	const titleInputRef = useRef( null );
	const previousFocusRef = useRef( null );

	useEffect( () => {
		// Handle hash navigation
		const handleHashChange = () => {
			if ( window.location.hash === '#create-idea' ) {
				openModal();
			}
		};

		window.addEventListener( 'hashchange', handleHashChange );

		// Check initial hash
		if ( window.location.hash === '#create-idea' ) {
			openModal();
		}

		// Intercept existing buttons with data-action="post-idea"
		const handleButtonClick = ( e ) => {
			const button = e.target.closest( '[data-action="post-idea"]' );
			if ( button ) {
				e.preventDefault();
				openModal();
			}
		};

		document.addEventListener( 'click', handleButtonClick );

		return () => {
			window.removeEventListener( 'hashchange', handleHashChange );
			document.removeEventListener( 'click', handleButtonClick );
		};
	}, [] );

	useEffect( () => {
		// Focus management for modal
		if ( isModalOpen && titleInputRef.current ) {
			titleInputRef.current.focus();
		}
	}, [ isModalOpen ] );

	const openModal = () => {
		// Already open, do nothing
		if ( isModalOpen ) {
			return;
		}
		previousFocusRef.current = document.activeElement;
		setIsModalOpen( true );
		setMessage( '' );
		// Only update URL if not already #create-idea
		if ( window.location.hash !== '#create-idea' ) {
			window.history.pushState( null, '', '#create-idea' );
		}
	};

	const closeModal = () => {
		setIsModalOpen( false );
		setFormData( {
			title: '',
			content: '',
			status: 'publish',
			genre: ''
		} );
		setMessage( '' );

		// Remove hash from URL
		if ( window.location.hash === '#create-idea' ) {
			window.history.pushState( null, '', window.location.pathname );
		}

		// Return focus to previous element
		if ( previousFocusRef.current ) {
			previousFocusRef.current.focus();
		}
	};

	const handleInputChange = ( e ) => {
		const { name, value } = e.target;
		setFormData( prev => ( {
			...prev,
			[ name ]: value
		} ) );
	};

	const handleSubmit = async ( e ) => {
		e.preventDefault();
		setIsLoading( true );
		setMessage( '' );

		try {
			const response = await fetch( '/wp-json/hametuha/v1/idea/mine/', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': wpApiSettings.nonce,
				},
				body: JSON.stringify( formData )
			} );

			const result = await response.json();

			if ( result.success ) {
				setMessage( result.message );
				setMessageType( 'success' );
				setTimeout( () => {
					if ( result.url ) {
						window.location.href = result.url;
					} else {
						closeModal();
					}
				}, 1500 );
			} else {
				setMessage( result.message || __( 'エラーが発生しました。', 'hametuha' ) );
				setMessageType( 'error' );
			}
		} catch ( error ) {
			setMessage( __( '通信エラーが発生しました。', 'hametuha' ) );
			setMessageType( 'error' );
		} finally {
			setIsLoading( false );
		}
	};

	const handleKeyDown = ( e ) => {
		if ( e.key === 'Escape' && isModalOpen ) {
			closeModal();
		}
	};

	return (
		<div className="ideas-component">
			{/* Floating Action Button */ }
			<button
				className="ideas-fab"
				onClick={ openModal }
				aria-label={ __( '新しいアイデアを投稿', 'hametuha' ) }
				title={ __( '新しいアイデアを投稿', 'hametuha' ) }
			>
				<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
					<path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z" />
				</svg>
			</button>

			{/* Modal */ }
			{ isModalOpen && (
				<div
					className="modal fade show d-block"
					role="dialog"
					aria-modal="true"
					aria-labelledby="ideaModalTitle"
					onKeyDown={ handleKeyDown }
					ref={ modalRef }
				>
					<div className="modal-dialog modal-lg">
						<div className="modal-content">
							{/* Modal Header */ }
							<div className="modal-header">
								<h5 className="modal-title" id="ideaModalTitle">
									{ __( '新しいアイデアを投稿', 'hametuha' ) }
								</h5>
								<button
									type="button"
									className="btn-close"
									onClick={ closeModal }
									aria-label={ __( '閉じる', 'hametuha' ) }
								/>
							</div>

							{/* Modal Body */ }
							<div className="modal-body">
								{/* Message */ }
								{ message && (
									<div
										className={ `alert ${ messageType === 'success' ? 'alert-success' : 'alert-danger' } mb-3` }
										role="alert"
										aria-live="polite"
									>
										{ message }
									</div>
								) }

								{/* Form */ }
								<form onSubmit={ handleSubmit }>
									{/* Title field */ }
									<div className="mb-3">
										<label htmlFor="ideaTitle" className="form-label">
											{ __( 'タイトル', 'hametuha' ) } <span className="text-danger">*</span>
										</label>
										<input
											type="text"
											className="form-control"
											id="ideaTitle"
											name="title"
											value={ formData.title }
											onChange={ handleInputChange }
											required
											ref={ titleInputRef }
											aria-describedby="ideaTitleHelp"
										/>
										<div id="ideaTitleHelp" className="form-text">
											{ __( 'アイデアのタイトルを入力してください', 'hametuha' ) }
										</div>
									</div>

									{/* Content field */ }
									<div className="mb-3">
										<label htmlFor="ideaContent" className="form-label">
											{ __( '内容', 'hametuha' ) } <span className="text-danger">*</span>
										</label>
										<textarea
											className="form-control"
											id="ideaContent"
											name="content"
											rows="5"
											value={ formData.content }
											onChange={ handleInputChange }
											required
											aria-describedby="ideaContentHelp"
										/>
										<div id="ideaContentHelp" className="form-text">
											{ __( 'アイデアの詳細を入力してください', 'hametuha' ) }
										</div>
									</div>

									{/* Genre field */ }
									<div className="mb-3">
										<label htmlFor="ideaGenre" className="form-label">
											{ __( 'ジャンル', 'hametuha' ) } <span className="text-danger">*</span>
										</label>
										<select
											className="form-select"
											id="ideaGenre"
											name="genre"
											value={ formData.genre }
											onChange={ handleInputChange }
											required
											aria-describedby="ideaGenreHelp"
										>
											<option value="">{ __( 'ジャンルを選択してください', 'hametuha' ) }</option>
											{ HametuhaIdeaTags && HametuhaIdeaTags.map( genre => (
												<option key={ genre.id } value={ genre.id }>
													{ genre.name }
												</option>
											) ) }
										</select>
										<div id="ideaGenreHelp" className="form-text">
											{ __( 'アイデアのジャンルを選択してください', 'hametuha' ) }
										</div>
									</div>

									{/* Status field */ }
									<div className="mb-3">
										<label htmlFor="ideaStatus" className="form-label">
											{ __( '公開設定', 'hametuha' ) }
										</label>
										<select
											className="form-select"
											id="ideaStatus"
											name="status"
											value={ formData.status }
											onChange={ handleInputChange }
											aria-describedby="ideaStatusHelp"
										>
											<option value="publish">{ __( '公開', 'hametuha' ) }</option>
											<option value="private">{ __( '非公開', 'hametuha' ) }</option>
										</select>
										<div id="ideaStatusHelp" className="form-text">
											{ __( 'アイデアの公開設定を選択してください', 'hametuha' ) }
										</div>
									</div>
								</form>
							</div>

							{/* Modal Footer */ }
							<div className="modal-footer">
								<button
									type="button"
									className="btn btn-secondary"
									onClick={ closeModal }
									disabled={ isLoading }
								>
									{ __( 'キャンセル', 'hametuha' ) }
								</button>
								<button
									type="submit"
									className="btn btn-primary"
									onClick={ handleSubmit }
									disabled={ isLoading }
								>
									{ isLoading ? (
										<>
											<span className="spinner-border spinner-border-sm me-2" role="status"
												aria-hidden="true" />
											{ __( '投稿中...', 'hametuha' ) }
										</>
									) : (
										__( '投稿する', 'hametuha' )
									) }
								</button>
							</div>
						</div>
					</div>
				</div>
			) }

			{/* Modal backdrop */ }
			{ isModalOpen && <div className="modal-backdrop fade show" /> }
		</div>
	);
};

// Initialize component when DOM is ready
document.addEventListener( 'DOMContentLoaded', () => {
	const container = document.createElement( 'div' );
	container.id = 'ideas-component-container';
	document.body.appendChild( container );
	createRoot( container ).render( <IdeasComponent /> );
} );
