/**
 * ユーザーのレビューを表示する
 */

/*global HametuhaReviews: true*/
/*global google: true*/

(function ($) {
    'use strict';

    google.load('visualization', '1.1', {packages:["bar"]});
    google.setOnLoadCallback(function(){
        var data = {
            cols: [
                {
                    label: '名前',
                    id   : 'label',
                    type : 'string'
                },
                {
                    label: 'まっとう',
                    id   : 'positive',
                    type : 'number'
                },
                {
                    label: '破滅的',
                    id   : 'negative',
                    type : 'number'
                }
            ],
            rows: []
        };
        for(var prop in HametuhaReviews){
            if( HametuhaReviews.hasOwnProperty(prop) ){
                (function(r){
                    data.rows.push({
                        c: [
                            {
                                v: r.genre,
                                f: r.genre
                            },
                            {
                                v: r.positive.value,
                                f: r.positive.label + ' (' + r.positive.value + ')'
                            },
                            {
                                v: r.negative.value,
                                f: r.negative.label + ' (' + r.negative.value + ')'
                            }
                        ]
                    });
                })(HametuhaReviews[prop]);
            }
        }
        var chart = new google.visualization.ChartWrapper({
            chartType: 'Bar',
            containerId: document.getElementById('review-graph')
        });
        chart.setDataTable(data);
        chart.setOptions(google.charts.Bar.convertOptions({
            chart: {
                title: 'これまでに投稿者が集めたレビューの総数'
            },
            bars: 'horizontal',
            backgroundColor: '#f9f9f9',
            legend: {
                position: 'none'
            }
        }));
        chart.draw();
        // Resize chart
        var timer = null;
        $(window).resize(function(){
            if( timer ){
                clearTimeout(timer);
            }
            timer = setTimeout(function(){
                chart.draw();
            }, 500);
        });
    });


})(jQuery);
