/*!
 * Login helper
 */


jQuery(document).ready(function ($) {

  'use strict';

  var $divider = $('.login-form-divider');

  if ( $divider.length ) {

    $.each( [
      '#reg_passmail',
      '.forgetmenot',
      'p.submit'
    ], function( index, selector ) {
      $divider.before( $(selector) );
    } );

    $('.login-action-register p.submit input').val('利用規約に同意して登録');

  }

});
