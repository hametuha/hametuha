/**
 * Description
 */
!function(t){"use strict";t(document).ready(function(){var e,i=t(".image-picker","#wpbody-content"),n=i.find(".new-img");i.on("click",".button-primary",function(t){t.preventDefault(),e||(e=wp.media.frames.haMediaFrame=wp.media({className:"media-frame ha-media-frame",frame:"select",multiple:!1,title:"使用する画像をアップロードまたは選択してください。",library:{type:"image"},button:{text:"選択した画像を選ぶ"}}),e.on("select",function(){var t,a=e.state().get("selection").first().toJSON();t=a.sizes.pinky?a.sizes.pinky.url:a.sizes.full.url,n.attr("src",t),i.find("input").val(a.id),i.find("p").effect("highlight")})),e.open()}),i.on("click",".button",function(t){t.preventDefault(),n.attr("src",n.attr("data-src")),i.find("input").val(""),i.find("p").effect("highlight")})})}(jQuery);
//# sourceMappingURL=../map/components/edit-profile-helper.js.map
