/**
 * Description
 */
!function(e){"use strict";e(document).ready(function(){var t,n,c=e("#become-author-form");c.length&&(n=c.find("input[type=submit]"),c.submit(function(e){t=c.find("input[name=review_contract]:checked"),e.preventDefault(),t.length?c.ajaxSubmit({dataType:"json",success:function(e){Hametuha.alert(e.message,!e.success),e.success?setTimeout(function(){window.location.href=e.url},5e3):t.length&&n.prop("disabled",!1)},error:function(){Hametuha.alert("更新に失敗しました。時間を置いてからもう一度やり直してください。",!0)}}):Hametuha.alert("利用規約に同意されていません。",!0)})),e("#become-login-check").click(function(){e(this).prop("checked")?e("#become-login-button").attr("disabled",!1):e("#become-login-button").attr("disabled",!0)})})}(jQuery);
//# sourceMappingURL=../map/components/become-author.js.map
