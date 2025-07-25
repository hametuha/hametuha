/*!
 * Login helper
 */

/* global HametuhaLoginHelper: false */

jQuery( document ).ready( function( $ ) {

  'use strict';

  // Fix divider order.
  var $divider = $( '.login-form-divider' );
  if ( $divider.length ) {
    $.each( [
      '#reg_passmail',
      '.forgetmenot',
      'p.submit'
    ], function( index, selector ) {
      $divider.before( $( selector ) );
    } );

    $('.login-action-register p.submit input').val( HametuhaLoginHelper.submitLabel );
  }

  // Add placeholder and documents.
  $( '.login-action-register #user_login' )
    .attr( 'placeholder', HametuhaLoginHelper.loginPlaceHolder )
    .after( '<span class="description">' + HametuhaLoginHelper.loginDescription + '</span>' );

  $( '.login-action-register #user_email' )
    .attr( 'placeholder', HametuhaLoginHelper.emailPlaceholder );
});
