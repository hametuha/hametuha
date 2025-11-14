/*!
 * Header component.
 *
 * @handle hametuheader
 * @deps cookie-tasting-heartbeat, wp-element
 *
 */
/* global CookieTasting: false */

const { Component, createRoot } = wp.element;
const { addQueryArgs } = wp.url;

class HametuHeaderEmpty extends Component {
	render() {
		return (
			<li style={ { display: 'none' } } />
		)
	}
}

class HametuHeader extends Component {

	constructor( props ) {
		super( props );
		this.state = Object.assign( {
			login_url: '/wp-login.php?redirect_to=' + location.pathname,
			register: '/wp-login.php?action=register',
			lastChecked: 0,
			notifications: [],
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
			Hametuha.alert( e.message || 'エラーが発生しました。' );
		} );
	}

	getLogoutUrl() {
		return addQueryArgs( '/wp-login.php', {
			action: 'logout',
			_wpnonce: CookieTasting.get( 'logout' ),
		} );
	}

	getStateValue() {
		return {
			logout: this.getLogoutUrl(),
			loggedIn: CookieTasting.isLoggedIn(),
			avatar: CookieTasting.get( 'avatar' ) || '',
			name: CookieTasting.userName(),
			profile: CookieTasting.get( 'profile' ),
			role: CookieTasting.get( 'role' ),
			isAuthor: !!CookieTasting.get( 'is_author' ),
		}
	}

	render() {
		return (
			<ul className="navbar-nav ms-auto navbar-login navbar-login--user nav nav-pills col-sm-1 justify-content-end">

				{ this.state.loggedIn ? (
					<li className="nav-item dropdown">
						<a href="#" className="nav-link dropdown-toggle dropdown--notify"
							data-bs-toggle="dropdown"
							aria-expanded="false"
							data-last-checked={ this.state.lastChecked }>
							<i className="icon-earth" />
						</a>
						<ul id="notification-container" className="dropdown-menu dropdown-menu-end notification__container">
							{ this.state.notifications.length ? (
								this.state.notifications.map( ( notification, int ) => {
									return (
										<li className='notification__item--header'
											key={ int }
											dangerouslySetInnerHTML={ { __html: notification.rendered } } />
									)
								} )
							) : (
								<li>
									<span>お知らせはなにもありません。</span>
								</li>
							) }
							<li className="text-center notification__more">
								<a href="/dashboard/notifications/all">
									通知一覧へ
									<i className="icon-arrow-right4" />
								</a>
							</li>
						</ul>
					</li>
				) : null }

				{ this.state.loggedIn ? (
					<li className="nav-item dropdown">
						<a href="#" className="nav-link dropdown-toggle"
							data-bs-toggle="dropdown"
							aria-expanded="false">
							<img className='avatar' src={ this.state.avatar } alt={ this.state.name } />
						</a>
						<ul className="dropdown-menu dropdown-menu-end">
							<li className="dropdown-item-text greeting">
								<strong>{ this.state.name }</strong>さん<br />
								<span className="role">{ this.state.role }</span>
							</li>
							<li><hr className="dropdown-divider" /></li>
							<li>
								<a href="/dashboard" className="dropdown-item">
									<i className="icon-cog" />
									ダッシュボード
								</a>
							</li>

							{ this.state.isAuthor ? (
								<>
									<li>
										<a href="/dashboard/works" className="dropdown-item">
											<i className="icon-dashboard" />
											作品管理
										</a>
									</li>
									<li>
										<a href="/dashboard/statistics/popular" className="dropdown-item">
											<i className="icon-chart" />
											統計情報
										</a>
									</li>
									<li>
										<a href={ this.state.profile } className="dropdown-item">
											<i className="icon-user"></i>
											プロフィール
										</a>
									</li>
									<li>
										<a href="/wp-admin/" className="dropdown-item">
											<i className="icon-wordpress"></i>
											管理画面
										</a>
									</li>
								</>
							) : null }

							<li><hr className="dropdown-divider" /></li>
							<li>
								<a href="/dashboard/reading/comments/" className="dropdown-item">
									<i className="icon-bubble-dots" />
									あなたのコメント
								</a>
							</li>
							<li>
								<a href="/your/lists/" className="dropdown-item">
									<i className="icon-drawer3" />
									あなたのリスト
								</a>
							</li>
							<li>
								<a href="/dashboard/reading/reviews/" className="dropdown-item">
									<i className="icon-star2" />
									レビューした作品
								</a>
							</li>
							<li>
								<a href="/ideas/mine/" className="dropdown-item">
									<i className="icon-lamp4" />
									アイデア帳
								</a>
							</li>
							<li>
								<a href="/dashboard/friends/" className="dropdown-item">
									<i className="icon-heart5" />
									フォロワー
								</a>
							</li>

							<li><hr className="dropdown-divider" /></li>
							<li>
								<a href={ this.state.logout } className="dropdown-item">
									<i className="icon-exit4" />
									ログアウト
								</a>
							</li>
						</ul>
					</li>
				) : null }

				{ this.state.loggedIn ? null : (
					<li className="login-buttons">
						<a className="login" href={ this.state.login_url } onClick={ ( e ) => {
							this.handleClick( e )
						} }>ログイン</a>
						<a className="register" href={ this.state.register } onClick={ ( e ) => {
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

createRoot( document.getElementById( 'user-info' ) ).render( <HametuHeader /> );
