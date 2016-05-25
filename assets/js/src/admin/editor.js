/**
 * Description
 */

(function ($) {
  'use strict';


  $(document).ready(function () {

    // タグ
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

    // よくある質問タグ
    $('.taxonomy-check-list').on('click', '.taxonomy-check-box', function(){
      var tags = [],
          $p = $(this).parents('.taxonomy-check-list');
      $p.find('input:checked').each(function(index, cb){
        tags.push($(cb).val());
      });
      $p.prev('input').val(tags.join(', '));
    });
  });



})(jQuery);
