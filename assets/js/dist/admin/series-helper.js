!function(t){"use strict";t(document).ready(function(){var e=t("#series-posts-list");e.sortable({axis:"y",handle:".dashicons-menu",opacity:.8,placeholder:"sortable-placeholder",containment:"parent",update:function(){var e=t(this).find("li"),n=e.length;e.each(function(e,a){t(a).find("input[name^=series_order]").val(n-e)})}}).on("click",".button--delete",function(n){if(n.preventDefault(),window.confirm("この作品を作品集から除外しますか？")){var a=t(this).parents("li");t.post(e.attr("data-endpoint"),{action:"series_list",_seriesordernonce:e.attr("data-nonce"),series:e.attr("data-post-id"),post_id:t(this).attr("data-id")}).done(function(t){t.success?a.remove():alert(t.message)}).fail(function(){}).always(function(){})}})})}(jQuery);
//# sourceMappingURL=../map/admin/series-helper.js.map