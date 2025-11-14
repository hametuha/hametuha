/*!
 * フロントページで読み込むスクリプト
 *
 * @deps jquery, chart-js
 */

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
            // Canvas APIは2025年現在全ブラウザでサポート済み、念のため最小限のチェック
            if( radar.get(0).getContext ){
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
    });

})(jQuery);
