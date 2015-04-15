/**
 * Description
 */

(function ($) {
    'use strict';

    $('#epub-previewer').change(function(e){
        var val = $(this).val(),
            $form = $('<form target="epub-preview">' +
            '<input type="hidden" name="direction" />' +
            '<input type="hidden" name="post_id" />' +
            '</form>');
        if( val.length ){
            $form.attr('action', $(this).attr('data-endpoint'));
            $form.find('input[name=direction]').val( $('input[name=orientation]:checked').val() == 'vertical' ? 'rtl' : 'ltr' );
            $form.find('input[name=post_id]').val(val);
            $form.submit();
        }
    });

})(jQuery);
