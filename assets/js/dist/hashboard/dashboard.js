/*!
 * wpdeps=hb-components-post-list,hb-plugins-toast
 */
!function(n){Vue.component("hametuha-notification-block",{data:function(){return{limit:3,notifications:[],loading:!1}},props:{link:{type:String,required:!0}},template:'<div class="hb-post-list"><div class="hb-post-list-list"><div v-for="n in notifications" class="notification-loop notification-loop-small" v-html="n.rendered"></div></div><a :href="link" class="btn btn-block btn-secondary">もっと読む</a><hb-loading title="読み込み中……" :loading="loading"></hb-loading></div>',mounted:function(){var l=this;l.loading=!0,n.hbRest("GET","hametuha/v1/notifications/all",{paged:1}).done(function(i,n,t){for(var o=[],a=0;a<i.length&&(o.push(i[a]),!(a+1>=l.limit));a++);l.notifications=o}).fail(n.hbRestError()).always(function(){l.loading=!1})}}),n(document).on("click","#slack-invitation",function(i){i.preventDefault(),n.hbRest("POST","hameslack/v1/invitation/me").done(function(i){Hashboard.toast(i.message)}).fail(n.hbRestError()).always(function(){})})}(jQuery);
//# sourceMappingURL=../map/hashboard/dashboard.js.map
