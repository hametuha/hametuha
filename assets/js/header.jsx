/*!
 *  Global header component
 *
 * @handle hametuheader
 * @deps cookie-tasting-heartbeat, wp-element, wp-i18n, hametuha-alert, headroom
 */
/* global CookieTasting: false */
/* global Headroom: false */

const { Component, render } = wp.element;
const { addQueryArgs } = wp.url;
const { __ } = wp.i18n;
const { HametuHeaderVars } = window;

/**
 * Render icon
 *
 * @param {Object} props
 * @returns {JSX.Element}
 */
const BootstrapIcon = ( props ) => {
	let { size, name } = props;
	if ( ! size ) {
		size = 16;
	}
	return (
		<svg className={ 'hametuha-bi hametuha-bi-' + name } fill="currentColor" width={ size } height={ size } viewBox="0,0,16,16">
			<use xlinkHref={ HametuHeaderVars.svg + '#' + name } />
		</svg>
	);
};

window.Hametuha.BootstrapIcon = BootstrapIcon;

class HametuHeader extends Component {

	constructor( props ) {
		super( props );
		this.state = Object.assign( {
			login_url: '/wp-login.php?redirect_to=' + location.pathname,
			register: '/wp-login.php?action=register',
			lastChecked: 0,
			notifications: [],
			toggleProfile: false,
			toggleNotification: false,
		}, this.getStateValue() );
	}

	componentDidMount() {
		// Observe Cookie change.
		jQuery( 'html' ).on( 'cookie.tasting.updated', () => {
			this.setState( this.getStateValue() );
		} );
		// Check notifications.
		this.checkNotifications();
		setInterval( () => {
			this.checkNotifications();
		}, 60 * 1000 );
	}

	checkNotifications() {
		// Only when user is logged in.
		if ( !this.state.loggedIn ) {
			return;
		}
		// Retrieve notifications.
		CookieTasting.testBefore().then( res => {
			return wp.apiFetch( {
				path: 'hametuha/v1/notifications/recent',
			} );
		} ).then( response => {
			this.setState( { notifications: response } );
		} ).catch( e => {
			Hametuha.alert( e.message || __( 'エラーが発生しました。', 'hametuha' ) );
		} );
	}

	getLogoutUrl() {
		return addQueryArgs( '/wp-login.php', {
			action: 'logout',
			_wpnonce: CookieTasting.get( 'logout' ),
			redirect_to: encodeURIComponent( window.location.pathname ),
		} );
	}

	getStateValue() {
		return {
			logout: this.getLogoutUrl(),
			loggedIn: CookieTasting.isLoggedIn(),
			avatar: CookieTasting.get( 'avatar' ) || '',
			name: CookieTasting.userName(),
			role: CookieTasting.get( 'role' ),
			isAuthor: !!CookieTasting.get( 'is_author' ),
		}
	}

	render() {
		return (
			<ul className="user-login-actions">

				{ this.state.loggedIn ? (

					<li className="user-login-action">
						<button className={ 'user-login-dropdown' + ( this.state.toggleProfile ? ' active' : '' ) } onClick={ () => {
							this.setState( {
								toggleProfile: ! this.state.toggleProfile,
								toggleNotification: false,
							} );
						} }>
							<img loading="eager" className='user-login-avatar' src={ this.state.avatar }
								alt={ this.state.name } />
						</button>
						{ this.state.toggleProfile && (
							<ul className="user-login-dropdown-menu user-login-links">
								<li className="greeting">
									<strong>{ this.state.name }</strong>さん<br />
									<span className="role">{ this.state.role }</span>
								</li>
								<li className="divider" />
								<li>
									<a href="/dashboard">
										<BootstrapIcon name="speedometer" />
										<span>{ __( 'ダッシュボード', 'hametuha' ) }</span>
									</a>
								</li>

								{ this.state.isAuthor ? (
									<li>
										<a href="/wp-admin/edit.php">
											<BootstrapIcon name="book" />
											<span>{ __( '作品一覧', 'hametuha' ) }</span>
										</a>
									</li>
								) : null }

								<li className="divider" />
								<li>
									<a href="/your/comments/">
										<BootstrapIcon name="chat" />
										<span>{ __( 'あなたのコメント', 'hametuha' ) }</span>
									</a>
								</li>
								<li>
									<a href="/your/lists/">
										<BootstrapIcon name="list-check" />
										<span>{ __( 'あなたのリスト', 'hametuha' ) }</span>
									</a>
								</li>
								<li>
									<a href="/your/reviews/">
										<BootstrapIcon name="star-fill" />
										<span>{ __( 'レビューした作品', 'hametuha' ) }</span>
									</a>
								</li>
								<li>
									<a href="/my/ideas/">
										<BootstrapIcon name="lightbulb" />
										<span>{ __( 'アイデア帳', 'hametuha' ) }</span>
									</a>
								</li>
								<li>
									<a href="/doujin/follower/">
										<BootstrapIcon name="heart-fill" />
										<span>{ __( 'フォロワー', 'hametuha' ) }</span>
									</a>
								</li>

								<li className="divider" />
								<li>
									<a href={ this.state.logout }>
										<BootstrapIcon name="box-arrow-right" />
										<span>{ __( 'ログアウト', 'hametuha' ) }</span>
									</a>
								</li>
							</ul>
						) }
					</li>
				) : null }

				{ this.state.loggedIn ? (
					<li className="user-login-action">
						<button className={ 'user-login-dropdown' + ( this.state.toggleNotification ? ' active' : '' ) } data-last-checked={ this.state.lastChecked } onClick={ () => {
							this.setState( {
								toggleProfile: false,
								toggleNotification: ! this.state.toggleNotification,
							} );
						} }>
							<svg width="24" height="24" fill="currentColor" className="bi bi-bell user-login-notification-icon" viewBox="0 0 16 16">
								<path
									d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2zM8 1.918l-.797.161A4.002 4.002 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4.002 4.002 0 0 0-3.203-3.92L8 1.917zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5.002 5.002 0 0 1 13 6c0 .88.32 4.2 1.22 6z" />
							</svg>
						</button>
						{ this.state.toggleNotification && (
							<ul id="notification-container" className="user-login-dropdown-menu">
								{ this.state.notifications.length ? (
									this.state.notifications.map( ( notification ) => {
										return (
											<li key={ notification.id } className='notification__item--header'
												dangerouslySetInnerHTML={ { __html: notification.rendered } } />
										)
									} )
								) : (
									<li className="notification__empty">
										<span>{ __( 'お知らせはなにもありません。', 'hametuha' ) }</span>
									</li>
								) }
								<li className="divider" />
								<li className="notification__more">
									<a href="/dashboard/notifications/all">
										<span>{ __( '通知一覧へ', 'hametuha' ) }</span>
									</a>
								</li>
							</ul>
						) }
					</li>
				) : null }


				{ this.state.loggedIn ? null : (
					<li className="user-login-action">
						<a className="user-login-link" href={ this.state.login_url } onClick={ ( e ) => {
							this.handleClick( e )
						} }>ログイン</a>
						<a className="user-login-link" href={ this.state.register } onClick={ ( e ) => {
							this.handleClick( e )
						} }>登録</a>
					</li>
				) }

			</ul>
		);
	}

	/**
	 * Handle normal click
	 *
	 * @param {MouseEvent} e
	 */
	handleClick( e ) {
		e.preventDefault();
		const link = e.currentTarget;
		link.classList.add( 'disabled' );
		CookieTasting.testBefore().then( () => {
			this.setState( this.getStateValue() );
		} ).catch( () => {
			window.location.href = link.href;
		} ).finally( () => {
			link.classList.remove( 'disabled' );
		} );
	}
}

render( <HametuHeader />, document.getElementById( 'user-info' ) );


const header = document.getElementById( 'header' );
if ( header ) {
	const headroom = new Headroom( header, {
		onPin: function () {
			jQuery( 'body' ).removeClass( 'header-hidden' );
		},
		onUnpin: function () {
			jQuery( 'body' ).addClass( 'header-hidden' );
		}
	} );
	headroom.init();
}
