/**
 * Common UI Parts
 */

/*global Hametuha: true*/

import Headroom from './headroom';

jQuery( document ).ready( function ( $ ) {
	'use strict';

	// ツールチップ
	$( '.help-tip' ).tooltip( {
		trigger: 'hover focus click',
		container: 'body',
	} );

	// プロフィールページのナビ
	const profileNav = $( '#profile-navi' );
	if ( profileNav.length ) {
		const profileNavs = {};
		$( 'section', '#your-profile' ).each( function ( index, section ) {
			const id = 'profile-section-' + ( index + 1 );
			$( section ).attr( 'id', id );
			profileNavs[ id ] = $( section )
				.find( 'h2, h3:first-child' )
				.text();
		} );
		for ( const id in profileNavs ) {
			if ( profileNavs.hasOwnProperty( id ) ) {
				profileNav.append(
					'<li><a href="#' +
						id +
						'">' +
						profileNavs[ id ] +
						'</a></li>'
				);
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
			onPin() {
				$( 'body' ).removeClass( 'header-hidden' );
			},
			onUnpin() {
				$( 'body' ).addClass( 'header-hidden' );
			},
		} );
		headroom.init();
	}

	// ページ内リンク
	$( 'a.page-anker' ).click( function ( e ) {
		const $target = $( $( this ).attr( 'href' ) );
		if ( $target.length ) {
			e.preventDefault();
			$( 'body,html' ).animate(
				{
					scrollTop: $target.offset().top - 70,
				},
				300,
				'swing'
			);
		}
	} );
} );
