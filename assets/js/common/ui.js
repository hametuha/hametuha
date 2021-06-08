/*!
 * Hametuha UI
 *
 * @handle hametuha-ui
 * @deps jquery, headroom, jquery.mmenu
 */

/*global Headroom: false*/

const $ = jQuery;

$( document ).ready( function () {

	// mmenu
	$( "nav#header-navigation" ).mmenu( {
		iconPanels: true,
		navbars: [
			true, // trueを渡すと普通のタイトルバーが出る
			{
				position: 'top',
				content: [
					'<form class="mm-search" method="get" action="/"><input name="s" type="search" placeholder="サイト内を検索" /> </form>'
				]
			},
		]
	}, {
		offCanvas: {
			pageNodetype: '#whole-body'
		}
	} );

	// ツールチップ
	$( '.help-tip' ).tooltip( {
		trigger: 'hover focus click',
		container: 'body'
	} );

	// プロフィールページのナビ
	const profileNav = $( '#profile-navi' );
	if ( profileNav.length ) {
		const profileNavs = {};
		$( 'section', '#your-profile' ).each( function ( index, section ) {
			const id = 'profile-section-' + ( index + 1 );
			$( section ).attr( 'id', id );
			profileNavs[ id ] = $( section ).find( 'h2, h3:first-child' ).text();
		} );
		for ( const id in profileNavs ) {
			if ( profileNavs.hasOwnProperty( id ) ) {
				profileNav.append( '<li><a href="#' + id + '">' + profileNavs[ id ] + '</a></li>' );
			}
		}
	}

	// 書くボタン
	$( '.write-panel-btn' ).click( function ( e ) {
		e.preventDefault();
		$( '#write-panel' ).toggleClass( 'open' );
	} );

	// スクロール
	const header = document.getElementById( 'header' );
	if ( header ) {
		const headroom = new Headroom( header, {
			onPin: function () {
				$( 'body' ).removeClass( 'header-hidden' );
			},
			onUnpin: function () {
				$( 'body' ).addClass( 'header-hidden' );
			}
		} );
		headroom.init();
	}

} );
