/**
 * Description
 */
!function(a){"use strict";a(document).ready(function(){var t=a("#hametuha-tag-input");if(t.length){var n=a(".hametuha-tag-cb"),c=a(".hametuha-tag-extra"),e=function(){var t=[];n.each(function(n,c){a(c).attr("checked")&&t.push(a(c).val())}),a.each(c.val().replace("、",",").split(","),function(n,c){var e=a.trim(c);e.length&&t.push(e)}),a("#hametuha-tag-input").val(t.join(", "))};n.click(e);var i=[];a.each(t.val().split(", "),function(t,c){var e=!1;n.each(function(t,n){if(c==a(n).val())return e=!0,!1}),e||i.push(c)}),c.val(i.join(", ")),c.keyup(e)}a(".taxonomy-check-list").on("click",".taxonomy-check-box",function(){var t=[],n=a(this).parents(".taxonomy-check-list");n.find("input:checked").each(function(n,c){t.push(a(c).val())}),n.prev("input").val(t.join(", "))})})}(jQuery);
//# sourceMappingURL=../map/admin/editor.js.map
