/*!
 * Hametuha Dashboard - React version
 *
 * @handle hametuha-hb-dashboard
 * @deps wp-element, wp-api-fetch, wp-i18n, hametuha-loading-indicator, hametuha-toast
 */

const { createRoot, useState, useEffect } = wp.element;
const { __ } = wp.i18n;
const { apiFetch } = wp;

// Get hametuha theme components
const { LoadingIndicator, toast } = wp.hametuha;

/**
 * Notification block component for dashboard
 */
const NotificationBlock = ( { link, limit = 3 } ) => {
	const [ loading, setLoading ] = useState( false );
	const [ notifications, setNotifications ] = useState( [] );

	useEffect( () => {
		const fetchNotifications = async () => {
			setLoading( true );
			try {
				const response = await apiFetch( {
					path: '/hametuha/v1/notifications/all?paged=1',
				} );
				// Limit to specified number
				setNotifications( response.slice( 0, limit ) );
			} catch ( error ) {
				// Silent fail - dashboard notification block is not critical
				console.error( 'Failed to fetch notifications:', error );
			} finally {
				setLoading( false );
			}
		};

		fetchNotifications();
	}, [ limit ] );

	return (
		<div className="hb-post-list" style={ { position: 'relative' } }>
			<div className="hb-post-list-list">
				{ notifications.map( ( notification, index ) => (
					<div
						key={ `notification-${ index }` }
						className="notification-loop notification-loop-small"
						dangerouslySetInnerHTML={ { __html: notification.rendered } }
					/>
				) ) }
			</div>
			<a href={ link } className="btn btn-block btn-secondary">
				{ __( 'もっと読む', 'hametuha' ) }
			</a>
			<LoadingIndicator loading={ loading } />
		</div>
	);
};

// Mount the notification block component
const notificationContainer = document.getElementById( 'hametuha-notification-block' );
if ( notificationContainer ) {
	const link = notificationContainer.dataset.link || '';
	createRoot( notificationContainer ).render( <NotificationBlock link={ link } /> );
}

// Slack invitation button handler
const slackButton = document.getElementById( 'slack-invitation' );
if ( slackButton ) {
	slackButton.addEventListener( 'click', async ( e ) => {
		e.preventDefault();
		slackButton.disabled = true;

		try {
			const response = await apiFetch( {
				path: '/hameslack/v1/invitation/me',
				method: 'POST',
			} );

			if ( toast && response.message ) {
				toast( response.message, 'success' );
			}
		} catch ( error ) {
			const message = error?.message || __( 'エラーが発生しました。', 'hametuha' );
			if ( toast ) {
				toast( message, 'danger', __( 'エラー', 'hametuha' ) );
			}
		} finally {
			slackButton.disabled = false;
		}
	} );
}
