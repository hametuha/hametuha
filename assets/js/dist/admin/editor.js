!function(t){"use strict";t(document).ready(function(){var c=t(".hametuha-tag-cb");c.length&&c.click(function(){var n=[];c.each(function(c,a){t(a).attr("checked")&&n.push(t(a).val())}),t("#hametuha-tag-input").val(n.join(", "))}),t(".taxonomy-check-list").on("click",".taxonomy-check-box",function(){var c=[],n=t(this).parents(".taxonomy-check-list");n.find("input:checked").each(function(n,a){c.push(t(a).val())}),n.prev("input").val(c.join(", "))})})}(jQuery);
//# sourceMappingURL=../map/admin/editor.js.map
