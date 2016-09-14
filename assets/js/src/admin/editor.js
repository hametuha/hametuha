/**
 * Description
 */

(function ($) {
  'use strict';


  $(document).ready(function () {

    // タグ
    var $tagInput = $('#hametuha-tag-input');
    if ( $tagInput.length ) {
      var $inputs = $('.hametuha-tag-cb');
      var $extraInput  = $('.hametuha-tag-extra');
      var updateTagValue = function(){
        var tags = [];
        // チェックボックスを取得
        $inputs.each(function(index, input){
          if ( $(input).attr('checked') ) {
            tags.push( $(input).val() );
          }
        });
        // テキストエリアを取得
        $.each( $extraInput.val().replace('、', ',').split(','), function(index, tag){
          var t = $.trim(tag);
          if (t.length) {
            tags.push(t);
          }
        } );
        $('#hametuha-tag-input').val( tags.join(', ') );
      };
      // チェックボックスを監視
      $inputs.click(updateTagValue);
      // テキストエリアに使われていないタグを移植
      var extraTags = [];
      $.each( $tagInput.val().split(', '), function(index, tag){
        var found = false;
        $inputs.each(function(i, t){
          if (tag == $(t).val()) {
            found = true;
            return false;
          }
        });
        if(!found){
          extraTags.push(tag);
        }
      });
      $extraInput.val(extraTags.join(', '));
      $extraInput.keyup(updateTagValue);
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
