/**
 * Description
 */

/*global Modernizr: true*/
/*global Chart: true*/
/*global HametuhaGenreStatic: true*/

(function ($) {
    'use strict';

    $(document).ready(function(){

        // レーダーチャート
        var radar = $('#genre-context'), ctx, chart, data = {
            labels: [],
            datasets: [{
              data: [],
              backgroundColor: []
            }]
        };
        if( radar.length ){
            if( Modernizr.canvas ){
                // データを加工する
                $.each(HametuhaGenreStatic.categories, function(index, cat){
                    if( index > 10 ){
                        return false;
                    }
                    data.labels.push(cat.name);
                    data.datasets[0].data.push(parseInt(cat.count, 10));
                    data.datasets[0].backgroundColor.push( 'rgba(255, 0, 0, ' + Math.min(1, Math.round((cat.count / HametuhaGenreStatic.total) * 0.8 * 10) / 10  + 0.2) + ')' );
                });
                ctx = radar.get(0).getContext('2d');
                chart = new Chart(ctx, {
                  type: 'doughnut',
                  data: data
                });
            }
        }

        // masonry
        var container = $('.frontpage-widget');
        // initialize Masonry after all images have loaded
        container.imagesLoaded( function() {
            container.masonry({
                itemSelector: '.col-sm-4'
            });
        });
    });

})(jQuery);
