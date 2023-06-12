/*!
 * wpdeps=hb-components-post-list,hb-plugins-toast
 */

/*global Vue: true*/

(function ($) {

  "use strict";

  Vue.component('hametuha-notification-block', {
    data: function(){
      return {
        limit: 3,
        notifications: [],
        loading: false
      };
    },
    props: {
      link: {
        type: String,
        required: true
      }
    },
    template: '<div class="hb-post-list">' +
      '<div class="hb-post-list-list">' +
        '<div v-for="n in notifications" class="notification-loop notification-loop-small" v-html="n.rendered"></div>' +
      '</div>' +
      '<a :href="link" class="btn btn-block btn-secondary">もっと読む</a>' +
      '<hb-loading title="読み込み中……" :loading="loading"></hb-loading>' +
    '</div>',

    mounted: function(){
      var self = this;
      self.loading = true;
      $.hbRest('GET', 'hametuha/v1/notifications/all', {paged: 1}).done(function(response, status, request){
        var store = [];
        for(var i = 0; i < response.length; i++){
          store.push(response[i]);
          if(i+1 >= self.limit){
            break;
          }
        }
        self.notifications = store;
      }).fail($.hbRestError()).always(function(){
        self.loading = false;
      });
    }
  });

  // Slack button
  $(document).on('click', '#slack-invitation', function(e){
    e.preventDefault();
    $.hbRest('POST', 'hameslack/v1/invitation/me').done(function(response){
      Hashboard.toast(response.message);
    }).fail($.hbRestError()).always(function(){

    });
  });

})(jQuery);
