/*!
 * ユーザーをピックアップするボックス
 *
 * @handle hametuha-user-picker
 * @deps jquery, wp-api
 */

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
	if ( e.keyCode === 13 ) {
		e.preventDefault();
		return false;
	}
} );

/*
 * Incremental Search
 */
let userPickerTimer,
	userPicking = false;
$( document ).on( 'keyup', '.user-picker__input', function ( e ) {
	if ( userPicking ) {
		return;
	}
	// If timer is set, clear.
	if ( userPickerTimer ) {
		clearTimeout( userPickerTimer );
	}
	const $input = $( this );
	const $container = $input.parents( '.user-picker' );
	const $lists = $input.next( '.user-picker__placeholder' );
	const templates = {};

	userPickerTimer = setTimeout( function () {
		userPicking = true;
		$lists.removeClass( 'empty' ).addClass( 'loading' );
		$lists.find( '.user-picker__item' ).each( function ( index, li ) {
			if ( !$( li ).find( '.user-picker__link.active' ).length ) {
				$( li ).remove();
			}
		} );
		$.ajax( {
			url: wpApiSettings.root + 'hametuha/v1/doujin/following/me/?s=' + $input.val(),
			method: 'GET',
			beforeSend: function ( xhr ) {
				xhr.setRequestHeader( 'X-WP-Nonce', wpApiSettings.nonce );
			}
		} ).done( function ( response ) {
			if ( response.users.length ) {
				const id = $container.attr( 'data-target' );
				if ( !templates.hasOwnProperty( id ) ) {
					templates[ id ] = $.templates( id + '-template' );
				}
				$.each( response.users, function ( index, user ) {
					$lists.append( templates[ id ].render( user ) );
				} );
			}
		} ).fail( function ( response ) {
			const message = response.responseJSON ? response.responseJSON.message : '失敗しました。';
			Hametuha.alert( message, 'error' );
		} ).always( function () {
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
	const $container = $( this ).parents( '.user-picker' );
	const ids = [];
	const max = parseInt( $container.attr( 'data-max' ), 10 );
	if ( ! $( this ).hasClass( 'active' ) ) {
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
