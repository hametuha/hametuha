/**
 * Description
 */

/*global Modernizr: true*/
/*global Chart: true*/

(function ($) {


    'use strict';

    // Make chart responsive
    Chart.defaults.global.responsive = true;

    $(document).ready(function(){
        // Date picker
        $('.datepicker').datepicker({
            dateFormat: 'yy-mm-dd',
            changeYear: true
        });

        // Chart
        if( !Modernizr.canvas ) {
            window.alert('あなたのブラウザではCanvasがサポートされていません。');
        }else{
            // Stats
            var stats = {};
            $('.stat').bind('update.analytics.hametuha', function(e, url, from, to){
                var $stat = $(this),
                    id = $stat.attr('id'),
                    action = $stat.attr('data-action'),
                    nonce = $stat.attr('data-nonce');
                if( !action || !nonce ){
                    return;
                }
                $stat.removeClass('no-data').addClass('loading');
                $.get(url, {
                    action: action,
                    _wpnonce: nonce,
                    from: from,
                    to: to
                }).done(function(data){
                    if( data ){
                        if( !stats[id] ){
                            stats[id] = new Chart($stat.find('canvas').get(0).getContext("2d"));
                        }
                        (stats[id][$stat.attr('data-type')])(data);
                    }else{
                        $stat.addClass('no-data');
                    }
                }).fail(function(xhr, status, error){
                    window.alert(error);
                }).always(function(){
                    $stat.removeClass('loading');
                });
            });
            // Form
            $('#analytics-date-form').submit(function(e){
                e.preventDefault();
                var $form = $(this);
                $('.stat').trigger('update.analytics.hametuha', [$form.attr('action'), $form.find("input[name=from]").val(), $form.find("input[name=to]").val()]);
            }).trigger('submit');
        }
    });



})(jQuery);
