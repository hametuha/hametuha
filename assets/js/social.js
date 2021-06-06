/*!
 * bodyタグの開始直後に読み込まれる
 *
 * @handle hametuha-social
 * @deps jquery
 */

/*global FB: false*/
/*global twttr: false*/
/*global HametuhaSocial: false */

const $ = jQuery;

//
// アウトバウンドを記録
// -------------------
//
$( document ).on( 'click', 'a[data-outbound]', function ( e ) {
	const url = $( this ).attr( 'href' );
	const category = $( this ).attr( 'data-outbound' );
	const action = $( this ).attr( 'data-action' );
	const label = $( this ).attr( 'data-label' );
	const value = $( this ).attr( 'data-value' ) || 1;
	if ( category && action && label ) {
		Hametuha.ga.eventOutbound( e, url, category, action, label, value );
	}
} );


//
// Facebook
// ------------------
//
window.fbAsyncInit = function () {
	// 初期化
	FB.init( {
		appId: '196054397143922',
		xfbml: true,
		autoLogAppEvents: true,
		version: 'v3.2'
	} );
	// イベントを監視
	const actions = {
		'comment.create': 'comment',
		'comment.remove': 'uncomment',
		'edge.create': 'like',
		'edge.remove': 'dislike',
		'message.send': 'message'
	};
	$.each( actions, function ( prop, action ) {
		FB.Event.subscribe( prop, function ( url ) {
			const href = url.hasOwnProperty( 'href' ) ? url.href : url;
			try {
				ga( 'send', {
					hitType: 'social',
					socialNetwork: 'facebook',
					socialAction: action,
					socialTarget: href.replace( /^https?:\/\/hametuha\.(com|info)/, '' )
				} );
			} catch ( err ) {
			}
		} );
	} );
};
( function ( d, s, id ) {
	if ( d.getElementById( id ) ) {
		return;
	}
	const js = d.createElement( s );
	const fjs = d.getElementsByTagName( s )[ 0 ];
	js.id = id;
	js.async = true;
	js.src = HametuhaSocial.needChat ? '//connect.facebook.net/ja_JP/sdk/xfbml.customerchat.js' : "//connect.facebook.net/ja_JP/sdk.js";
	fjs.parentNode.insertBefore( js, fjs );
}( document, 'script', 'facebook-jssdk' ) );


//
// Twitter
// ---------------
//
window.twttr = ( function ( d, s, id ) {
	if ( d.getElementById( id ) ) {
		return;
	}
	const js = d.createElement( s );
	const fjs = d.getElementsByTagName( s )[ 0 ];
	const t = window.twttr || {};
	js.id = id;
	js.src = "https://platform.twitter.com/widgets.js";
	js.async = true;
	fjs.parentNode.insertBefore( js, fjs );

	t._e = [];
	t.ready = function ( f ) {
		t._e.push( f );
	};

	return t;
}( document, "script", "twitter-wjs" ) );

// つぶやきを集計
twttr.ready( function ( twttr ) {
	$.each( [ 'follow', 'tweet', 'retweet', 'click', 'favorite' ], function ( index, key ) {
		twttr.events.bind( key, function () {
			try {
				ga( 'send', {
					hitType: 'social',
					socialNetwork: 'twitter',
					socialAction: key,
					socialTarget: window.location.pathname
				} );
			} catch ( err ) {
				// Do nothing
			}
		} );
	} );
} );
