/**
 * Description
 */
!function(e){"use strict";var t=function(t){e(t).select2({language:"ja",placeholder:"検索してください",maximumSelectionLength:1,minimumInputLength:2,templateResult:function(t){return e('<span class="user-selector-item"><img alt="" src="'+t.avatar+'" />'+t.text+"</span>")},ajax:{url:HametuhaUserSelect.endpoint+e(t).attr("data-mode"),dataType:"json",delay:300,data:function(t){return{s:t.term,mode:e(this).attr("data-mode"),_wpnonce:HametuhaUserSelect.nonce}},processResults:function(e,t){for(var a=[],n=0,r=e.length;n<r;n++)a.push({id:e[n].ID,text:e[n].name+"（"+e[n].role+"）",avatar:e[n].avatar});return{results:a,pagination:{more:!1}}},escapeMarkup:function(e){return e}}})};e(document).on("initialized.userSelect",".select",function(){t(this)}),e(document).ready(function(){e('select[data-module="user-select"]').each(function(e,a){t(a)})})}(jQuery);
//# sourceMappingURL=../map/components/user-select.js.map
