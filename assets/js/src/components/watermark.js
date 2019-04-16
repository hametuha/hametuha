/**
 * Watermark handler.
 */

jQuery(document).ready(function ($) {

  'use strict';

  var $watermark = $( '#watermark' );
  if ( $watermark.length ) {
    $watermark.click( function(e) {
      $(this).toggleClass( 'toggle' );
    } );
  }

});
