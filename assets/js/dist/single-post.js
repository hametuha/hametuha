Chart.defaults.global.responsive=!0,function(t){"use strict";Hametuha.views.Scroll=Backbone.View.extend({el:"#content-wrapper",events:{},tategaki:!1,scrollTop:0,ticker:null,slider:null,max:1e3,timer:0,toLast:!1,animating:!1,limit:null,initialize:function(){_.bindAll(this,"getSliderPosition","toggleScrollBind","updateTicker","tickerHandler","scrollHandler","moveTo","finishAnimate","passedTime"),this.ticker=t('<span id="slider-ticker">0%</span>'),this.tategaki=Hametuha.isTategaki(),this.slider=t("#slider"),this.limit=t("#post-author"),this.bindScroll=!0,this.scrollTop=t(window).scrollTop();var e=0;this.slider.slider({max:this.max,min:0,step:1,value:this.getSliderPosition(e),slide:this.updateTicker,change:this.updateTicker,stop:this.tickerHandler}),this.slider.find(".ui-slider-handle").append(this.ticker),t(window).on("resize scroll load",this.scrollHandler),t(window).on("toggle.scroll.hametuha",this.toggleScrollBind);var i=new Date;this.timer=i.getTime()},passedTime:function(){var t=new Date;return Math.round((t.getTime()-this.timer)/1e3)},toggleScrollBind:function(e,i){this.bindScroll=i,i&&t(window).scrollTop(this.scrollTop)},getSliderPosition:function(t){return this.tategaki?this.max-t:t},updateTicker:function(t,e){isNaN(e.value)||this.ticker.text(e.value/10+"%")},tickerHandler:function(t,e){this.moveTo(e.value/1e3)},moveTo:function(e){var i=(t(document).height()-t(window).height())*e;this.animating=!0,t("body, html").animate({scrollTop:i},"fast",this.finishAnimate)},finishAnimate:function(){this.animating=!1},scrollHandler:function(){if(this.bindScroll){this.scrollTop=t(window).scrollTop();var e=t(document).height()-t(window).height(),i=Math.min(1,Math.max(0,this.scrollTop/e)),a=this.limit.offset().top+50;this.animating||this.slider.slider("value",parseInt(1e3*i,10));var s=t("#work-end-ranker");if(!this.toLast&&a<this.scrollTop+t(window).height()&&s.length){this.toLast=!0;var n=s.attr("data-post");Hametuha.ga.hitEvent("read","complete",n),Hametuha.ga.hitEvent("read","passed",n,this.passedTime()),t("body").addClass("finish-reading")}}}}),jQuery(document).ready(function(t){t(".work-content p").each(function(e,i){Hametuha.str.startYakumono(t(i).text())||t(i).hasClass("wp-caption-text")||t(i).attr("style")||t(i).addClass("indent")});var e="viewing-content",i=t("#viewing-content"),a=t("#footer-single"),s=function(){i.attr("id",e).removeClass("show-nav"),t(window).trigger("toggle.scroll.hametuha",[!0])};a.on("click","a",function(n){if(n.preventDefault(),t(this).hasClass("active"))t(this).removeClass("active"),s();else{a.find("a").removeClass("active"),t(this).addClass("active");var r=t(this).attr("href").replace("#","");r.match(/reading-nav/)?i.attr("id",e).addClass("show-nav"):(i.attr("id","viewing-"+r.replace("-wrapper","")).removeClass("show-nav"),t(window).trigger("toggle.scroll.hametuha",[!1]),t(window).scrollTop(0))}}),t(".reset-viewer").click(function(t){t.preventDefault(),s(),a.find("a").removeClass("active")}),new Hametuha.views.Scroll;var n,r,o=t("#single-radar");o.length&&(Modernizr.canvas?(n=o.get(0).getContext("2d"),r=new Chart(n).Radar(postScore,{})):o.replaceWith('<p class="alert alert-danger">Canvasに対応していないため、ご利用の環境ではレーダーチャートを表示できません。</p>')),t(document).on("submit","#review-form",function(e){e.preventDefault();var i=t(this).find("input[type=submit]");i.button("loading"),t(this).ajaxSubmit({dataType:"json",success:function(t){i.button("reset"),t.error?Hametuha.alert(t.message,!0):(Hametuha.alert(t.message),i.button("complete"),t.guest&&setTimeout(function(){i.attr("disabled",!0)},50))},error:function(){Hametuha.alert("評価を送信できませんでした。",!0),i.button("reset")}})}),t(document).on("click",".star-rating i",function(){var e=parseInt(t(this).attr("data-value"),10);t(".star-rating i").each(function(i,a){parseInt(t(a).attr("data-value"),10)<=e?t(a).addClass("active"):t(a).removeClass("active")}),t(this).nextAll("input[type=hidden]").val(e)}),t(document).on("created.hametuha",".list-create-form",function(e,i){t("#list-changer").append('<div class="checkbox"><label><input type="checkbox" name="lists[]" value="'+i.ID+'" checked>'+("private"===i.post_status?"非公開: ":"公開　: ")+i.post_title+"</label></div>")})})}(jQuery);
//# sourceMappingURL=map/single-post.js.map