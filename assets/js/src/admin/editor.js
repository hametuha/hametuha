/**
 * Description
 */

(function ($) {
  'use strict';


  // タグ
  $(document).ready(function () {
    var $inputs = $('.hametuha-tag-cb');
    if ( $inputs.length ) {
      $inputs.click(function () {
        var tags = [];
        $inputs.each(function(index, input){
          if ( $(input).attr('checked') ) {
            tags.push( $(input).val() );
          }
        });
        $('#hametuha-tag-input').val( tags.join(', ') );
      });
    }
  });

})(jQuery);
