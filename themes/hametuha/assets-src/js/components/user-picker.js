/*!
 * ユーザーをピックアップするボックス
 *
 * @handle hametuha-components-user-picker
 * @deps jquery, wp-api-fetch, hametuha-toast
 */

/*global Hametuha: true*/

const { apiFetch } = wp;
const { toast } = wp.hametuha;
const $ = jQuery;

/**
 * Get parent container
 *
 * @param {Object} element
 * @returns {jQuery}
 */
function getParent( element ) {
	return $( element ).parents( '.user-picker' );
}

/*
 * Avoid Enter
 */
$( document ).on( 'keydown', '.user-picker__input', function ( e ) {
	if ( e.keyCode == 13 ) {
		e.preventDefault();
		return false;
	}
} );

/*
 * Incremental Search
 */
var userPickerTimer, userPicking = false;
$( document ).on( 'keyup', '.user-picker__input', function ( e ) {
	if ( userPicking ) {
		return;
	}
	// If timer is set, clear.
	if ( userPickerTimer ) {
		clearTimeout( userPickerTimer );
	}
	var $input = $( this ),
		$container = $input.parents( '.user-picker' ),
		$lists = $input.next( '.user-picker__placeholder' );

	userPickerTimer = setTimeout( function () {
		userPicking = true;
		$lists.removeClass( 'empty' ).addClass( 'loading' );
		$lists.find( '.user-picker__item' ).each( function ( index, li ) {
			if ( !$( li ).find( '.user-picker__link.active' ).length ) {
				$( li ).remove();
			}
		} );

		apiFetch( {
			path: '/hametuha/v1/doujin/following/me?s=' + $input.val()
		} ).then( ( response ) => {
			if ( response.users.length ) {
				$.each( response.users, function ( index, user ) {
					const userItem = `
						<li class="user-picker__item" data-user-id="${user.ID}">
							<a class="user-picker__link" href="#" data-user-id="${user.ID}">
								<img src="${user.avatar}"> ${user.display_name} <i class="icon-close"></i>
							</a>
						</li>
					`;
					$lists.append( userItem );
				} );
			}
		} ).catch( ( response ) => {
			var message = response.message || 'ユーザーを取得できませんでした。';
			toast( message, 'danger', 'エラー' );
		} ).finally( () => {
			$lists.removeClass( 'loading' );
			if ( !$lists.find( '.user-picker__item' ).length ) {
				$lists.addClass( 'empty' );
			}
			userPickerTimer = null;
			userPicking = false;
		} );
	}, 1000 );
} );

$( document ).on( 'click', '.user-picker__link', function ( e ) {
	e.preventDefault();
	$( this ).toggleClass( 'active' );
	var $container = $( this ).parents( '.user-picker' ),
		ids = [],
		max = parseInt( $container.attr( 'data-max' ), 10 );
	if ( $( this ).hasClass( 'active' ) ) {
	} else {
		$( this ).parent( 'li' ).remove();
		$container.removeClass( 'filled' );
	}
	$container.find( '.user-picker__link.active' ).each( function ( index, a ) {
		ids.push( $( a ).attr( 'data-user-id' ) );
	} );
	if ( max <= ids.length ) {
		$container.addClass( 'filled' );
		$container.find( '.user-picker__link:not(.active)' ).each( function ( i, notActive ) {
			$( notActive ).parent( 'li' ).remove();
		} );
	}
	$( $container.attr( 'data-target' ) ).val( ids.join( ',' ) );
} );
