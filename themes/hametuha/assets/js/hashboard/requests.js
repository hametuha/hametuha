/*!
 * HashBoard Requests - React version
 *
 * @handle hametuha-hb-requests
 * @deps wp-element, wp-api-fetch, wp-i18n, hametuha-loading-indicator, hametuha-pagination, hametuha-toast
 */

const { createRoot, useState, useEffect, useCallback } = wp.element;
const { __ } = wp.i18n;
const { apiFetch } = wp;

// Get hametuha theme components
const { LoadingIndicator, Pagination, toast } = wp.hametuha;

/**
 * Format date to localized string
 *
 * @param {string} dateString
 * @returns {string}
 */
const formatDate = ( dateString ) => {
	if ( ! dateString ) {
		return '';
	}
	const date = new Date( dateString );
	return date.toLocaleString( 'ja-JP', {
		year: 'numeric',
		month: 'short',
		day: 'numeric',
		hour: '2-digit',
		minute: '2-digit',
	} );
};

/**
 * Single request item component
 */
const RequestItem = ( { request, onApprove, onDeny } ) => {
	const approved = request.ratio >= 0;
	const revenue = Math.abs( parseInt( request.ratio, 10 ) );

	const handleApprove = () => {
		onApprove( request.id, request.post_id );
	};

	const handleDeny = () => {
		if ( window.confirm( __( '辞退してもよろしいですか？　この操作は取り消せません。', 'hametuha' ) ) ) {
			onDeny( request.id, request.post_id );
		}
	};

	return (
		<li className="hametuha-request-item list-group-item">
			<div className="d-flex w-100 justify-content-between mb-1">
				<h5>{ __( '共同編集者としての招待', 'hametuha' ) }</h5>
				<small>{ formatDate( request.updated ) }</small>
			</div>
			<p className="mb-1 text-muted">
				{ __( '作品集', 'hametuha' ) }『<a href={ request.permalink }>{ request.post_title }</a>』{ __( 'に', 'hametuha' ) } { request.name }{ __( 'さんが', 'hametuha' ) }（{ request.label }）{ __( 'として', 'hametuha' ) }
				{ approved ? (
					<strong>{ __( '参加中', 'hametuha' ) }</strong>
				) : (
					<span>{ __( '招待を受け付けています。', 'hametuha' ) }</span>
				) }
				{ __( '報酬はロイヤリティの', 'hametuha' ) } <strong>{ revenue }%</strong> { __( 'です。', 'hametuha' ) }
			</p>
			<p className="text-muted text-end">
				<small className="badge text-bg-light">{ __( 'アクション', 'hametuha' ) }</small>
			</p>
			<p className="text-end hametuha-request-actions">
				{ ! approved && (
					<button
						className="btn btn-success btn-sm me-2"
						onClick={ handleApprove }
					>
						{ __( '承諾する', 'hametuha' ) }
					</button>
				) }
				<button
					className="btn btn-danger btn-sm"
					onClick={ handleDeny }
				>
					{ __( '辞退する', 'hametuha' ) }
				</button>
			</p>
		</li>
	);
};

/**
 * Request list component
 */
const RequestList = ( { type } ) => {
	const [ loading, setLoading ] = useState( false );
	const [ requests, setRequests ] = useState( [] );
	const [ currentPage, setCurrentPage ] = useState( 1 );
	const [ totalPages, setTotalPages ] = useState( 0 );

	const fetchRequests = useCallback( async ( page ) => {
		setLoading( true );
		try {
			const response = await apiFetch( {
				path: `/hametuha/v1/collaborators/invitations/me?paged=${ page }`,
				parse: false,
			} );

			const totalPagesHeader = response.headers.get( 'X-WP-Total-Pages' );
			setTotalPages( parseInt( totalPagesHeader, 10 ) || 0 );
			setCurrentPage( page );

			const data = await response.json();
			setRequests( data );
		} catch ( error ) {
			const message = error?.message || __( 'エラーが発生しました。', 'hametuha' );
			if ( toast ) {
				toast( message, 'danger', __( 'エラー', 'hametuha' ) );
			}
		} finally {
			setLoading( false );
		}
	}, [] );

	useEffect( () => {
		fetchRequests( 1 );
	}, [ fetchRequests ] );

	const handleApprove = useCallback( async ( userId, postId ) => {
		setLoading( true );
		try {
			const response = await apiFetch( {
				path: '/hametuha/v1/collaborators/invitations/me',
				method: 'POST',
				data: {
					series_id: postId,
				},
			} );

			if ( toast ) {
				toast( response.message, 'success', __( '成功', 'hametuha' ) );
			}
			fetchRequests( currentPage );
		} catch ( error ) {
			let message = __( 'エラーが発生しました。', 'hametuha' );
			if ( error?.message ) {
				message += error.message;
			}
			if ( toast ) {
				toast( message, 'danger', __( 'エラー', 'hametuha' ) );
			}
			setLoading( false );
		}
	}, [ currentPage, fetchRequests ] );

	const handleDeny = useCallback( async ( userId, postId ) => {
		setLoading( true );
		try {
			const response = await apiFetch( {
				path: '/hametuha/v1/collaborators/invitations/me',
				method: 'DELETE',
				data: {
					series_id: postId,
				},
			} );

			if ( toast ) {
				toast( response.message, 'success', __( '成功', 'hametuha' ) );
			}
			fetchRequests( currentPage );
		} catch ( error ) {
			let message = __( 'エラーが発生しました。', 'hametuha' );
			if ( error?.message ) {
				message += error.message;
			}
			if ( toast ) {
				toast( message, 'danger', __( 'エラー', 'hametuha' ) );
			}
			setLoading( false );
		}
	}, [ currentPage, fetchRequests ] );

	const handlePageChange = useCallback( ( page ) => {
		fetchRequests( page );
	}, [ fetchRequests ] );

	return (
		<div className="hametuha-hb-request-list" style={ { position: 'relative' } }>
			{ totalPages > 0 && (
				<p className="text-muted text-end">
					{ currentPage } / { totalPages }{ __( 'ページ', 'hametuha' ) }
				</p>
			) }

			{ requests.length > 0 ? (
				<ul className="notification-loop-container list-group">
					{ requests.map( ( request, index ) => (
						<RequestItem
							key={ `${ request.id }-${ request.post_id }-${ index }` }
							request={ request }
							onApprove={ handleApprove }
							onDeny={ handleDeny }
						/>
					) ) }
				</ul>
			) : (
				! loading && (
					<div className="alert alert-secondary">
						<p className="mb-0">{ __( 'リクエストはありません。', 'hametuha' ) }</p>
					</div>
				)
			) }

			{ totalPages > 1 && (
				<Pagination
					current={ currentPage }
					total={ totalPages }
					onChange={ handlePageChange }
				/>
			) }

			<LoadingIndicator loading={ loading } />
		</div>
	);
};

// Mount the component
const container = document.getElementById( 'hametuha-requests' );
if ( container ) {
	const type = container.dataset.type || '';
	createRoot( container ).render( <RequestList type={ type } /> );
}
