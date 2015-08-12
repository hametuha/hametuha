/**
 * Description
 */

/*global google: true*/

(function ($) {

    'use strict';

    // Load visualization
    google.load('visualization', '1', null);

    // Register date picker
    $(document).ready(function(){
        // Date picker
        $('.datepicker').datepicker({
            dateFormat: 'yy-mm-dd',
            changeYear: true
        });
    });


    // Callback after chart is ready
    google.setOnLoadCallback(function(){

        // Stats store
        var stats = {};

        // Bind ajax event
        $('.stat').on('update.analytics.hametuha', function(e, url, from, to){
            var $stat = $(this),
                id = $stat.attr('id');
            $stat.removeClass('no-data').addClass('loading');
            $.get($stat.attr('data-endpoint'), {
                from: from,
                to: to
            }).done(function(result){
                if( result && result.data){
                    if( !stats[id] ){
                        stats[id] = new google.visualization.ChartWrapper({
                            chartType: $stat.attr('data-type'),
                            containerId: $stat.find('.stat__container').attr('id')
                        });
                    }
                    stats[id].setOptions(result.options);
                    if( result.data.cols ){
                        stats[id].setDataTable(result.data);
                    }else{
                        var dataSet = google.visualization.arrayToDataTable(result.data);
                        stats[id].setDataTable(dataSet);
                    }
                    stats[id].draw();
                }else{
                    $stat.addClass('no-data');
                }
            }).fail(function(xhr, status, error){
                window.alert(error);
            }).always(function(){
                $stat.removeClass('loading');
            });
        });


        // Bind form event and trigger immediately
        $('#analytics-date-form').submit(function(e){
            e.preventDefault();
            $('.stat').trigger('update.analytics.hametuha', [
                $(this).attr('action'),
                $(this).find("input[name=from]").val(),
                $(this).find("input[name=to]").val()
            ]);
        }).trigger('submit');


        // Resize chart
        var timer = null;
        $(window).resize(function(){
            if( timer ){
                clearTimeout(timer);
            }
            timer = setTimeout(function(){
                for( var id in stats ){
                    if( stats.hasOwnProperty(id) ){
                        stats[id].draw();
                    }
                }
            }, 500);
        });
    });

})(jQuery);
