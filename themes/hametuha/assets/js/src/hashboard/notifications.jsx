/*!
 * Hametuha notifications - React version
 *
 * @handle hametuha-hb-notifications
 * @deps wp-element, wp-api-fetch, wp-i18n, hametuha-loading-indicator, hametuha-pagination, hametuha-toast
 */

const { createRoot, useState, useEffect, useCallback } = wp.element;
const { __ } = wp.i18n;
const { apiFetch } = wp;

// Get hametuha theme components
const { LoadingIndicator, Pagination, toast } = wp.hametuha;

/**
 * Notification list component
 */
const NotificationList = ( { type } ) => {
	const [ loading, setLoading ] = useState( false );
	const [ notifications, setNotifications ] = useState( [] );
	const [ currentPage, setCurrentPage ] = useState( 1 );
	const [ totalPages, setTotalPages ] = useState( 0 );

	const fetchNotifications = useCallback( async ( page ) => {
		setLoading( true );
		try {
			const response = await apiFetch( {
				path: `/hametuha/v1/notifications/${ type }?paged=${ page }`,
				parse: false,
			} );

			const totalPagesHeader = response.headers.get( 'X-WP-TotalPages' );
			setTotalPages( parseInt( totalPagesHeader, 10 ) || 0 );
			setCurrentPage( page );

			const data = await response.json();
			setNotifications( data );
		} catch ( error ) {
			const message = error?.message || __( 'エラーが発生しました。', 'hametuha' );
			if ( toast ) {
				toast( message, 'danger', __( 'エラー', 'hametuha' ) );
			}
		} finally {
			setLoading( false );
		}
	}, [ type ] );

	useEffect( () => {
		fetchNotifications( 1 );
	}, [ fetchNotifications ] );

	const handlePageChange = useCallback( ( page ) => {
		fetchNotifications( page );
	}, [ fetchNotifications ] );

	return (
		<div className="hametuha-hb-notifications" style={ { position: 'relative' } }>
			{ totalPages > 0 && (
				<p className="text-muted text-end">
					{ currentPage } / { totalPages }{ __( 'ページ', 'hametuha' ) }
				</p>
			) }

			<div className="notification-loop-container">
				{ notifications.map( ( notification, index ) => (
					<div
						key={ `notification-${ index }` }
						className="notification-loop"
						dangerouslySetInnerHTML={ { __html: notification.rendered } }
					/>
				) ) }
			</div>

			{ notifications.length === 0 && ! loading && (
				<div className="alert alert-secondary">
					<p className="mb-0">{ __( 'お知らせはありません。', 'hametuha' ) }</p>
				</div>
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
const container = document.getElementById( 'hametuha-notifications' );
if ( container ) {
	const type = container.dataset.type || 'all';
	createRoot( container ).render( <NotificationList type={ type } /> );
}
