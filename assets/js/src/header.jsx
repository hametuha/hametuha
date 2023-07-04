/*!
 * Header component.
 *
 * @handle hametuheader
 * @deps cookie-tasting-heartbeat, wp-element
 *
 */
/* global CookieTasting: false */

const {Component, render} = wp.element;
const { addQueryArgs } = wp.url;

class HametuHeaderEmpty extends Component {
  render() {
    return (
      <li style={{display: 'none'}} />
    )
  }
}

class HametuHeader extends Component {

  constructor(props) {
    super(props);
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
    },  60 * 1000 );
  }

  checkNotifications() {
    // Only when user is logged in.
    if ( ! this.state.loggedIn ) {
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
      redirect_to: encodeURIComponent( window.location.pathname ),
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
      isAuthor: !! CookieTasting.get( 'is_author' ),
    }
  }

  render() {
    return (
      <ul className="navbar-nav navbar-right navbar-login navbar-login--user nav nav-pills col-sm-1">

        {this.state.loggedIn ? (

          <li className="dropdown">
            <a href="#" className="dropdown-toggle"
               data-toggle="dropdown">
              <img className='avatar' src={this.state.avatar} alt={this.state.name} />
            </a>
            <ul className="dropdown-menu">
              <li className="greeting">
                <strong>{this.state.name}</strong>さん<br/>
                <span className="role">{this.state.role}</span>
              </li>
              <li className="divider"/>
              <li>
                <a href="/dashboard">
                  <i className="icon-cog"/>
                  ダッシュボード
                </a>
              </li>

              {this.state.isAuthor ? (
				  <>
					  <li>
						  <a href="/dashboard/works">
							  <i className="icon-dashboard"/>
							  作品管理
						  </a>
					  </li>
					  <li>
						  <a href="/dashboard/statistics/">
							  <i className="icon-chart"/>
							  統計情報
						  </a>
					  </li>
					  <li>
						  <a href={ this.state.profile }>
							<i className="icon-user"></i>
							プロフィール
						  </a>
					  </li>
				  </>
              ) : null }

              <li className="divider"/>
              <li>
                <a href="/your/comments/">
                  <i className="icon-bubble-dots"/>
                  あなたのコメント
                </a>
              </li>
              <li>
                <a href="/your/lists/">
                  <i className="icon-drawer3"/>
                  あなたのリスト
                </a>
              </li>
              <li>
                <a href="/your/reviews/">
                  <i className="icon-star2"/>
                  レビューした作品
                </a>
              </li>
              <li>
                <a href="/my/ideas/">
                  <i className="icon-lamp4"/>
                  アイデア帳
                </a>
              </li>
              <li>
                <a href="/doujin/follower/">
                  <i className="icon-heart5"/>
                  フォロワー
                </a>
              </li>

              <li className="divider"/>
              <li>
                <a href={this.state.logout}>
                  <i className="icon-exit4"/>
                  ログアウト
                </a>
              </li>
            </ul>
          </li>
        ) : null }

        {this.state.loggedIn ? (
          <li className="dropdown">
            <a href="#" className="dropdown-toggle dropdown--notify" data-toggle="dropdown"
               data-last-checked={this.state.lastChecked}>
              <i className="icon-earth"/>
            </a>
            <ul id="notification-container" className="dropdown-menu notification__container">
              { this.state.notifications.length ? (
                this.state.notifications.map( ( notification ) => {
                  return (
                    <li className='notification__item--header' dangerouslySetInnerHTML={{__html: notification.rendered}} />
                  )
                } )
              ) : (
                <li>
                  <span>お知らせはなにもありません。</span>
                </li>
              )}
              <li className="text-center notification__more">
                <a href="/dashboard/notifications/all">
                  通知一覧へ
                  <i className="icon-arrow-right4"/>
                </a>
              </li>
            </ul>
          </li>
        ) : null }



        { this.state.loggedIn ? null : (
          <li className="login-buttons">
            <a href={this.state.login_url} onClick={(e) => { this.handleClick( e ) }}>ログイン</a>
            <a href={this.state.register} onClick={(e) => { this.handleClick( e ) }}>登録</a>
          </li>
        )}

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
    }).catch( () => {
      window.location.href = link.href;
    }).finally(() => {
      link.classList.remove( 'disabled' );
    });
  }
}

render( <HametuHeader/>, document.getElementById( 'user-info' ) );
