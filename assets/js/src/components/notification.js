/**
 * Description
 */

/*global HametuhaNotification: true*/

(function ($) {
    'use strict';

    $(document).ready(function(){
        var $list = $('#notification-link'),
            $link = $list.find('a.dropdown-toggle');
        // Check if latest info exists


        function updateNew(){
            var max = 0,
                checked = parseInt($link.attr('data-last-checked'), 10);
            $list.find('li[data-time]').each(function(index, elt){
                max = Math.max(max, parseInt($(elt).attr('data-time'), 10));
            });
            if( checked > max ){
                $link.removeClass('has-notify');
            }else{
                $link.addClass('has-notify');
            }
        }


        if( $list.length ){

            updateNew();
            
            // Check notice every 10 seconds
            setInterval(function(){

                $.get(HametuhaNotification.retrieve, {
                    _wpnonce: HametuhaNotification.nonce
                }).done(function(result){
                    if( result.length ){
                        var $divider = $list.find(".divider");
                        $divider.prevAll('li').remove();
                        for( var i = 0, l = result.length; i < l; i++){
                            $divider.before($(result[i]));
                        }
                    }
                    updateNew();
                });
            }, 15000);

            // If opened, update show time.
            $list.on('show.bs.dropdown', function(){
                $.post(HametuhaNotification.endpoint, {
                    _wpnonce: HametuhaNotification.nonce
                }).done(function(result){
                    $link.attr('data-last-checked', result.checked);
                    updateNew();
                });
            });

        }


    });

})(jQuery);
