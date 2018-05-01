/*!
 * wpdeps=hb-components-loading,hashboard-rest,hb-components-pagination
 */

(function($){

  'use strict';

  new Vue({
    el: '#hametuha-notifications',
    template: '<div class="hametuha-hb-notifications" data-loading-wrapper="true">' +
      '<div v-if="total"><p class="text-muted text-right">{{curPage}} / {{total}}ページ</p></div>' +
      '<div class="notification-loop-container">' +
        '<div class="notification-loop" v-for="n in notifications" v-html="n.rendered"></div>' +
      '</div>' +
      '<hb-pagination v-if="total" :total="total" :current="curPage" @pageChanged="pagination"></hb-pagination>' +
      '<hb-loading :loading="loading" title="読み込み中……"></hb-loading>' +
    '</div>',
    data: {
      type: '',
      curPage: 1,
      total: 0,
      loading: false,
      notifications: []
    },
    props: {
      type: {
        type: String,
        required: true
      }
    },
    beforeMount: function(){
      this.type = this.$el.getAttribute('data-type');
    },
    mounted: function () {
      this.loading = true;
      this.fetch(1);
    },

    methods: {
      fetch: function(page){
        var self = this;
        self.loading = true;
        $.hbRest('GET', 'hametuha/v1/notifications/' + this.type, {paged: page}).done(function(response, status, request){
          self.notifications = response;
          self.curPage = page;
          self.total = parseInt(request.getResponseHeader('X-WP-TotalPages'), 10);
        }).fail($.hbRestError()).always(function(){
          self.loading = false;
        });
      },

      pagination: function(number){
        this.fetch(number);
      }
    }
  });

})(jQuery);

