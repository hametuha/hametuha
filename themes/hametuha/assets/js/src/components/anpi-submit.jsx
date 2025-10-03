/*!
 * 安否報告投稿用のスクリプト
 *
 * @handle hametuha-components-anpi-submit
 * @deps wp-element, wp-i18n, wp-api-fetch
 */

const { useState, useRef, useEffect, createRoot } = wp.element;
const { __ } = wp.i18n;
const { apiFetch } = wp;

const AnpiSubmitComponent = () => {
	const [ isModalOpen, setIsModalOpen ] = useState( false );
	const [ isLoading, setIsLoading ] = useState( false );
	const [ message, setMessage ] = useState( '' );
	const [ messageType, setMessageType ] = useState( '' );
	const [ content, setContent ] = useState( '' );

	const modalRef = useRef( null );
	const contentInputRef = useRef( null );
	const previousFocusRef = useRef( null );

	useEffect( () => {
		// Intercept existing .anpi-new buttons
		const handleButtonClick = ( e ) => {
			const anpiButton = e.target.closest( '.anpi-new' );
			if ( anpiButton ) {
				e.preventDefault();
				openModal();
				return;
			}
		};

		document.addEventListener( 'click', handleButtonClick );

		return () => {
			document.removeEventListener( 'click', handleButtonClick );
		};
	}, [] );

	useEffect( () => {
		// Focus management for modal
		if ( isModalOpen && contentInputRef.current ) {
			contentInputRef.current.focus();
		}
	}, [ isModalOpen ] );

	const openModal = () => {
		if ( isModalOpen ) {
			return;
		}
		previousFocusRef.current = document.activeElement;
		setContent( '' );
		setIsModalOpen( true );
		setMessage( '' );
	};

	const closeModal = () => {
		setIsModalOpen( false );
		setContent( '' );
		setMessage( '' );

		// Restore focus
		if ( previousFocusRef.current ) {
			previousFocusRef.current.focus();
		}
	};

	const handleSubmit = async ( e ) => {
		e.preventDefault();

		if ( ! content.trim() ) {
			setMessage( __( '内容を入力してください。', 'hametuha' ) );
			setMessageType( 'error' );
			return;
		}

		setIsLoading( true );
		setMessage( '' );

		try {
			const response = await apiFetch( {
				path: '/hametuha/v1/anpi/new/',
				method: 'POST',
				data: {
					content: content.trim(),
				},
			} );

			if ( response.success ) {
				setMessage( __( 'めつかれさまでした。安否報告を受け付けました。', 'hametuha' ) );
				setMessageType( 'success' );
				setTimeout( () => {
					closeModal();
					// Reload page to show new anpi
					window.location.reload();
				}, 1500 );
			} else {
				throw new Error( response.message || __( '投稿に失敗しました。', 'hametuha' ) );
			}
		} catch ( error ) {
			setMessage( error.message || __( '投稿に失敗しました。', 'hametuha' ) );
			setMessageType( 'error' );
		} finally {
			setIsLoading( false );
		}
	};

	const handleKeyDown = ( e ) => {
		// Escape key to close modal
		if ( e.key === 'Escape' && ! isLoading ) {
			closeModal();
		}
	};

	if ( ! isModalOpen ) {
		return null;
	}

	return (
		<div
			className="modal fade show"
			style={ { display: 'block', backgroundColor: 'rgba(0,0,0,0.5)' } }
			onClick={ ( e ) => {
				if ( e.target === e.currentTarget && ! isLoading ) {
					closeModal();
				}
			} }
			onKeyDown={ handleKeyDown }
			role="dialog"
			aria-modal="true"
			aria-labelledby="anpi-modal-title"
		>
			<div className="modal-dialog" ref={ modalRef }>
				<div className="modal-content">
					<div className="modal-header">
						<h5 className="modal-title" id="anpi-modal-title">
							{ __( '安否報告', 'hametuha' ) }
						</h5>
						<button
							type="button"
							className="btn-close"
							onClick={ closeModal }
							disabled={ isLoading }
							aria-label={ __( '閉じる', 'hametuha' ) }
						>
							<span aria-hidden="true">&times;</span>
						</button>
					</div>

					<form onSubmit={ handleSubmit }>
						<div className="modal-body">
							{ message && (
								<div
									className={ `alert alert-${ messageType === 'error' ? 'danger' : 'success' }` }
									role="alert"
								>
									{ message }
								</div>
							) }

							<div className="form-group">
								<label htmlFor="anpi-content">
									{ __( '安否情報の内容', 'hametuha' ) }
								</label>
								<textarea
									id="anpi-content"
									className="form-control"
									rows="5"
									value={ content }
									onChange={ ( e ) => setContent( e.target.value ) }
									disabled={ isLoading }
									ref={ contentInputRef }
									placeholder={ __( '安否情報を入力してください...', 'hametuha' ) }
									required
								/>
							</div>
						</div>

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
								disabled={ isLoading || ! content.trim() }
							>
								{ isLoading ? __( '送信中...', 'hametuha' ) : __( '投稿する', 'hametuha' ) }
							</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	);
};

// Mount component
document.addEventListener( 'DOMContentLoaded', () => {
	const container = document.createElement( 'div' );
	container.id = 'anpi-submit-container';
	document.body.appendChild( container );

	const root = createRoot( container );
	root.render( <AnpiSubmitComponent /> );
} );
