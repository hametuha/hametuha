!function(t){"use strict";window.Hametuha={ga:{hitEvent:function(t,e,a,n,i){try{"undefined"==typeof n&&(n=1),ga("send",{hitType:"event",eventCategory:t,eventAction:e,eventLabel:a,eventValue:n,nonInteraction:!!i})}catch(o){}},eventOutbound:function(t,e,a,n,i,o){try{"undefined"==typeof o&&(o=1),ga("send",{hitType:"event",eventCategory:a,eventAction:n,eventLabel:i,eventValue:o,hitCallback:function(){Modernizr.touch?window.location.href=e:"share"===a?window.open(e,"outbound","width=520, height=350"):window.open(e,"outbound")}}),t.preventDefault()}catch(s){}}},str:{yakumono:/^[ 　【】《〔〝『「（”"'’\(\)]/,startYakumono:function(t){return this.yakumono.test(t)}},alert:function(e,a){t.notify({message:e},{type:a?"danger":"success",placement:{align:"center"}})},confirm:function(t,e,a){bootbox.dialog({title:"確認",message:t,buttons:{cancel:{label:"キャンセル",className:"btn-default"},ok:{label:a?"実行":"OK",className:a?"btn-danger":"btn-success",callback:e}}})},isTategaki:function(){return t("body").hasClass("tategaki")},models:{},views:{},collections:{},modal:{open:function(e,a){var n=t("#hametu-modal");n.find(".modal-title").html(e),"function"==typeof a?(n.addClass("loading"),a(n)):n.find(".modal-body").html(a),n.modal("show")},close:function(){var e=t("#hametu-modal");e.find(".modal-title").html(""),e.find(".modal-body").html(""),e.modal("hide")}}}}(jQuery),jQuery(document).ready(function(t){"use strict";var e=window.Hametuha;t("form").submit(function(){t(this).find("input[type=submit]").attr("disabled",!0)}),t(".pseudo-uploader").each(function(e,a){var n=t(a).next("input");t(a).on("click",".btn",function(t){t.preventDefault(),n.trigger("click")}),n.change(function(e){var n=t(this).val().split("\\");t(a).find("input[type=text]").val(n[n.length-1])})}),t(".form-unlimiter").click(function(e){t(this).parents("form").find("input[type=submit]").prop("disabled",!t(this).attr("checked"))}),t("a[data-confirm], input[data-confirm]").click(function(e){return window.confirm(t(this).attr("data-confirm"))?void 0:!1}),t("[data-toggle=offcanvas]").click(function(){t("body").toggleClass("offcanvas-on")}),t(".help-tip").tooltip({trigger:"hover focus click",container:"body"});var a=t("#profile-navi");if(a.length){var n={};t("section","#your-profile").each(function(e,a){var i="profile-section-"+(e+1);t(a).attr("id",i),n[i]=t(a).find("h2, h3:first-child").text()});for(var i in n)n.hasOwnProperty(i)&&a.append('<li><a href="#'+i+'">'+n[i]+"</a></li>")}t(".validator").submit(function(e){t(this).find("runtime-error").remove();var a=[];t(this).find(".required").each(function(e,n){if(!t(n).val()){t(n).addClass("erro");var i=t("label[for="+t(n).attr("id")+"]",this);i.length&&a.push(i.text()+"は必須項目です。")}}),a.length&&e.preventDefault()}),t(document).on("click","a.list-creator",function(a){a.preventDefault();var n=t(this).attr("href");e.modal.open(t(this).attr("title"),function(e){t.get(n,{},function(t){e.removeClass("loading"),e.find(".modal-body").html(t.html)})})}),t(document).on("submit",".list-create-form",function(a){a.preventDefault();var n=t(this);n.addClass("loading"),n.ajaxSubmit({success:function(t){t.success?n.trigger("created.hametuha",[t.post]):e.alert(t.message,!0),n.find("input[type=submit]").attr("disabled",!1),n.removeClass("loading")}})}),t(".modal").on("created.hametuha",".list-create-form",function(a,n){e.modal.close(),e.ga.hitEvent("list","add",n.ID),t("body").hasClass("single-lists")&&location.reload()}),t(document).on("submit",".list-save-manager",function(e){e.preventDefault();var a=t(this);a.addClass("loading"),a.ajaxSubmit({success:function(e){a.find("input[type=submit]").attr("disabled",!1),a.removeClass("loading");var n=t('<div class="alert alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">閉じる</span></button></div>');n.addClass("alert-"+(e.success?"success":"danger")).append("<span>"+e.message+"</span>"),a.prepend(n),setTimeout(function(){n.find("button").trigger("click")},5e3)}})}),t(document).on("click",".list-eraser",function(a){a.preventDefault(),e.confirm(t(this).attr("title"),function(){t.post(t(this).attr("href"),function(a){e.alert(a.message),a.success&&t("body").hasClass("single-lists")&&(window.location.href=a.url)})},!0)});var o=t("#my-list-deleter");o.length&&(t("ol.media-list > li").each(function(e,a){t(a).find(".list-inline").append(o.render({postId:t(a).attr("data-post-id")}))}),t("ol.media-list").on("click",".deregister-button",function(a){a.preventDefault();var n=t(this);e.confirm("リストからこの作品を削除します。この操作は取り消せませんが、よろしいですか？",function(){t.post(n.attr("href"),{},function(a){a.success?(n.parents("li.media").remove(),t("ol.media-list > li").length||(t("ol.media-list").before('<div class="alert alert-danger">'+a.message+"</div>"),setTimeout(function(){window.location.href=a.home_url},3e3))):e.alert(a.message,!0)})},!0)})),t(".row--share").each(function(e,a){var n=t(this);t.get(n.attr("data-share-url")).done(function(t){if(t.success)for(var e in t.result)t.result.hasOwnProperty(e)&&n.find("a.share--"+e+" span").text(t.result[e])}).fail(function(){}).always(function(){})}),t(document).on("click","a.share",function(e){var a=window.Hametuha.ga,n=t(this).attr("data-medium"),i=t(this).attr("href"),o=t(this).attr("data-target");switch(n){case"facebook":try{FB.ui({method:"share",href:i},function(t){t&&a.hitEvent("share",n,o)}),e.preventDefault()}catch(s){}break;case"hatena":break;default:a.eventOutbound(e,i,"share",n,o)}}),t(document).on("click","a[data-outbound]",function(a){var n=t(this).attr("href"),i=t(this).attr("data-outbound"),o=t(this).attr("data-action"),s=t(this).attr("data-label"),r=t(this).attr("data-value")||1;i&&o&&s&&e.ga.eventOutbound(event,n,i,o,s,r)});var s=setInterval(function(){if(window.FB&&window.FB.Event){clearInterval(s);var t={create:"like",remove:"dislike"};for(var e in t)t.hasOwnProperty(e)&&!function(t,e){FB.Event.subscribe("edge."+t,function(t){try{ga("send",{hitType:"social",socialNetwork:"facebook",socialAction:e,socialTarget:t.replace(/^https?:\/\/hametuha\.(com|info)/,"")})}catch(a){}})}(e,t[e])}},100),r=setInterval(function(){if(window.twttr&&window.twttr.events){clearInterval(r);for(var t=["follow","tweet","retweet","click","favorite"],e=0;e<t.length;e++)!function(t){window.twttr.events.bind(t,function(e){try{ga("send",{hitType:"social",socialNetwork:"twitter",socialAction:t,socialTarget:window.location.pathname})}catch(a){}})}(t[e])}},100)});
//# sourceMappingURL=map/common.js.map