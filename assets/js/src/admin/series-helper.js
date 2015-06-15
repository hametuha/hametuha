/*!
 * Series edit helper
 */

/*global Backbone: true*/
/*global _: true*/

(function ($) {
    'use strict';


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
                        alert(result.message);
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
