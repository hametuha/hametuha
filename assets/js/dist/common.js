!function(t){"use strict";window.Hametuha={ga:{hitEvent:function(t,e,a,n,i){try{"undefined"==typeof n&&(n=1),ga("send",{hitType:"event",eventCategory:t,eventAction:e,eventLabel:a,eventValue:n,nonInteraction:!!i})}catch(o){}},eventOutbound:function(t,e,a,n,i){try{ga("send",{hitType:"event",eventCategory:a,eventAction:n,eventLabel:i,hitCallback:function(){window.location.href=e}}),t.preventDefault()}catch(o){}}},str:{yakumono:/^[ 　【】《〔〝『「（”"'’\(\)]/,startYakumono:function(t){return this.yakumono.test(t)}},alert:function(t,e){window.alert(t)},isTategaki:function(){return t("body").hasClass("tategaki")},models:{},views:{},collections:{},modal:{open:function(e,a){var n=t("#hametu-modal");n.find(".modal-title").html(e),"function"==typeof a?(n.addClass("loading"),a(n)):n.find(".modal-body").html(a),n.modal("show")},close:function(){var e=t("#hametu-modal");e.find(".modal-title").html(""),e.find(".modal-body").html(""),e.modal("hide")}}}}(jQuery),jQuery(document).ready(function(t){"use strict";var e=window.Hametuha;t("form").submit(function(){t(this).find("input[type=submit]").attr("disabled",!0)}),t(".pseudo-uploader").each(function(e,a){var n=t(a).next("input");t(a).on("click",".btn",function(t){t.preventDefault(),n.trigger("click")}),n.change(function(e){var n=t(this).val().split("\\");t(a).find("input[type=text]").val(n[n.length-1])})}),t(".form-unlimiter").click(function(e){t(this).parents("form").find("input[type=submit]").prop("disabled",!t(this).attr("checked"))}),t("a[data-confirm], input[data-confirm]").click(function(e){return window.confirm(t(this).attr("data-confirm"))?void 0:!1}),t("[data-toggle=offcanvas]").click(function(){t("body").toggleClass("offcanvas-on")}),t(".help-tip").tooltip({trigger:"hover focus click",container:"body"});var a=t("#profile-navi");if(a.length){var n={};t("section","#your-profile").each(function(e,a){var i="profile-section-"+(e+1);t(a).attr("id",i),n[i]=t(a).find("h2, h3:first-child").text()});for(var i in n)n.hasOwnProperty(i)&&a.append('<li><a href="#'+i+'">'+n[i]+"</a></li>")}t(".validator").submit(function(e){t(this).find("runtime-error").remove();var a=[];t(this).find(".required").each(function(e,n){if(!t(n).val()){t(n).addClass("erro");var i=t("label[for="+t(n).attr("id")+"]",this);i.length&&a.push(i.text()+"は必須項目です。")}}),a.length&&e.preventDefault()}),t(document).on("click","a.list-creator",function(a){a.preventDefault();var n=t(this).attr("href");e.modal.open(t(this).attr("title"),function(e){t.get(n,{},function(t){e.removeClass("loading"),e.find(".modal-body").html(t.html)})})}),t(document).on("submit",".list-create-form",function(e){e.preventDefault();var a=t(this);a.addClass("loading"),a.ajaxSubmit({success:function(t){t.success?a.trigger("created.hametuha",[t.post]):window.alert(t.message),a.find("input[type=submit]").attr("disabled",!1),a.removeClass("loading")}})}),t(".modal").on("created.hametuha",".list-create-form",function(a,n){e.modal.close(),e.ga.hitEvent("list","add",n.ID),t("body").hasClass("single-lists")&&location.reload()}),t(document).on("submit",".list-save-manager",function(e){e.preventDefault();var a=t(this);a.addClass("loading"),a.ajaxSubmit({success:function(e){a.find("input[type=submit]").attr("disabled",!1),a.removeClass("loading");var n=t('<div class="alert alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">閉じる</span></button></div>');n.addClass("alert-"+(e.success?"success":"danger")).append("<span>"+e.message+"</span>"),a.prepend(n),setTimeout(function(){n.find("button").trigger("click")},5e3)}})}),t(document).on("click",".list-eraser",function(e){e.preventDefault(),window.confirm(t(this).attr("title"))&&t.post(t(this).attr("href"),function(e){window.alert(e.message),e.success&&t("body").hasClass("single-lists")&&(window.location.href=e.url)})});var o=t("#my-list-deleter");o.length&&(t("ol.media-list > li").each(function(e,a){t(a).find(".list-inline").append(o.render({postId:t(a).attr("data-post-id")}))}),t("ol.media-list").on("click",".deregister-button",function(a){a.preventDefault();var n=t(this);window.confirm("リストからこの作品を削除します。この操作は取り消せませんが、よろしいですか？")&&t.post(n.attr("href"),{},function(a){a.success?(n.parents("li.media").remove(),t("ol.media-list > li").length||(t("ol.media-list").before('<div class="alert alert-danger">'+a.message+"</div>"),setTimeout(function(){window.location.href=a.home_url},3e3))):e.alert(a.message)})})),t(".row--share").each(function(e,a){var n=t(this);t.get(n.attr("data-share-url")).done(function(t){if(t.success)for(var e in t.result)t.result.hasOwnProperty(e)&&n.find("a.share--"+e+" span").text(t.result[e])}).fail(function(){}).always(function(){})}),t(document).on("click","a.share",function(e){var a=window.Hametuha.ga,n=t(this).attr("data-medium"),i=t(this).attr("href"),o=t(this).attr("data-target");switch(n){case"facebook":try{e.preventDefault(),FB.ui({method:"share",href:i},function(t){t&&a.hitEvent("share",n,o)})}catch(s){}break;default:a.eventOutbound(e,i,"share",n,o)}})});
//# sourceMappingURL=map/common.js.map