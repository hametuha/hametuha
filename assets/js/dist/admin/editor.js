!function(t){"use strict";t("#epub-previewer").change(function(e){var i=t(this).val(),n=t('<form target="epub-preview"><input type="hidden" name="direction" /><input type="hidden" name="post_id" /></form>');i.length&&(n.attr("action",t(this).attr("data-endpoint")),n.find("input[name=direction]").val("vertical"==t("input[name=orientation]:checked").val()?"rtl":"ltr"),n.find("input[name=post_id]").val(i),n.submit())})}(jQuery);
//# sourceMappingURL=../map/admin/editor.js.map