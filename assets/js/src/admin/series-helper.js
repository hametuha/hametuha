/*!
 * Series edit helper
 */

/*global Backbone: true*/
/*global _: true*/

(function ($) {
    'use strict';

    // 本文プレビュー
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

    // その他のプレビュー
    $('#series-additons-list').on('click', 'a', function(e){
        var url = $(this).attr('href') + '?direction=' + ( 'vertical' === $('input[name="orientation"]:checked').val() ? 'rtl' : 'ltr' );
        e.preventDefault();
        window.open(url, $(this).attr('target'));
    });

    // 並び順
    $(document).ready(function(){
        var $sorter = $('#series-posts-list');
        $sorter.sortable({
            axis: 'y',
            handle: '.dashicons-menu',
            opacity: 0.8,
            placeholder: "sortable-placeholder",
            containment: "parent",
            update: function(){
                var $lis = $(this).find('li'),
                    start = $lis.length;
                $lis.each(function(index, li){
                    $(li).find('input[name^=series_order]').val(start - index);
                });
            }
        }).on('click', '.button--delete', function(e){
            e.preventDefault();
            if( window.confirm('この作品を作品集から除外しますか？') ){
                var $li = $(this).parents('li');
                $.post($sorter.attr('data-endpoint'), {
                    action: 'series_list',
                    _seriesordernonce: $sorter.attr('data-nonce'),
                    series: $sorter.attr('data-post-id'),
                    post_id: $(this).attr('data-id')
                }).done(function(result){
                    if( result.success ){
                        $li.remove();
                    }else{
                        window.alert(result.message);
                    }
                }).fail(function(){
                    // Do nothing
                }).always(function(){
                    // Do nothing
                });
            }
        });

    });

})(jQuery);
