/*!
 * wpdeps=hashboard,hb-components-bar-chart,hb-components-month-selector, hb-plugins-date
 */

/*global Vue: true*/

(function ($) {

  'use strict';

  var app = new Vue({
    el: '#access-container',
    data: function () {
      var now = new Date();
      var year = now.getFullYear();
      var month = ( '0' + ( now.getMonth() + 1 ) ).slice(-2);
      return {
        loading: false,
        from: [year, month, '01'].join('-'),
        to: [year, month, $.hbGetLastDateOfMonth(year, month)].join('-'),
        rankings: [],
        records: [],
        chartData: {},
        options: {
          responsive: true,
          maintainAspectRatio: false,
          tooltips: {
            enabled: true,
            mode: 'index',
            callbacks: {
              label: function (tooltipItems, data) {
                return tooltipItems.yLabel + 'PV（' + data.datasets[tooltipItems.datasetIndex].label + '）';
              }
            }
          },
          scales: {
            yAxes: [{
              stacked: true
            }]
          }
        }
      }
    },
    computed: {},

    mounted: function () {
      this.fetch();
    },
    methods: {
      filledLabels: function (from, to) {
        var start = new Date(from);
        var end = new Date(to);
        var labels = [];
        for (; start <= end; start.setDate(start.getDate() + 1)) {
          labels.push(start.getFullYear() + '-' + ('0' + (start.getMonth() + 1)).slice(-2) + '-' + ('0' + start.getDate()).slice(-2));
        }
        return labels;
      },

      /**
       * Get empty label
       *
       * @param length
       * @returns {Array}
       */
      skeleton: function (length) {
        var array = [];
        for (var i = 0; i < length; i++) {
          array.push(0);
        }
        return array;
      },

      dateChangeHandler: function (year, month) {
        month = ('0' + month).slice(-2);
        this.from = [year, month, '01'].join('-');
        this.to   = [year, month, $.hbGetLastDateOfMonth(year, month)].join('-');
        this.fetch();
      },

      fillDataSet: function (dataSets, labels, type, date, pv) {
        if (!dataSets[type]) {
          dataSets[type] = this.skeleton(labels.length);
        }
        var index = labels.indexOf(date);
        if (index > -1) {
          dataSets[type][index] += pv;
        }
      },

      fetch: function () {
        this.loading = true;
        var self = this;
        $.hbRest('GET', $('#access-container').attr('data-endpoint'), {
          from: this.from,
          to: this.to
        }).done(function (response) {
          // Set ranking
          var rankings = [];
          var curRank, j;
          for (var i = 0, l = response.rankings.length; i < l; i++) {
            curRank = 0;
            for (j = 0; j < l; j++) {
              if (response.rankings[j].pv > response.rankings[i].pv) {
                curRank++;
              } else {
                break;
              }
            }
            response.rankings[i].rank = curRank + 1;
          }
          self.rankings = response.rankings;
          // Create graph
          var chartData = {};
          var labels = self.filledLabels(response.start, response.end);
          var data_sets = {};
          $.each(response.records, function (index, record) {
            self.fillDataSet(data_sets, labels, record.post_type, record.date, record.pv);
          });

          chartData.labels = labels;
          chartData.datasets = [];
          var colors = [
            [255, 12, 62],
            [0, 153, 232],
            [247, 124, 0],
            [95, 125, 140],
            [52, 143, 55],
            [255, 59, 0],
            [124, 19, 164],
            [68, 90, 101],
            [0, 184, 214],
            [110, 76, 64]
          ];
          var colorIndex = 0;
          for (var prop in data_sets) {
            if (!data_sets.hasOwnProperty(prop)) {
              continue;
            }
            var color = colors[colorIndex].join(', ');
            chartData.datasets.push({
              type: 'line',
              lineTension: 0,
              label: prop,
              data: data_sets[prop],
              backgroundColor: 'rgb(' + color + ')',
              borderColor: 'rgba(' + color + ', .6)'
            });
            colorIndex++;
          }
          self.chartData = chartData;
          self.records = response.records;
        }).fail($.hbRestError()).always(function () {
          self.loading = false;
        })
      }
    }
  });


})(jQuery);
