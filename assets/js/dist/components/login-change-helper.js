!function(e){"use strict";e(document).ready(function(){var a=e("#change-login-form"),n=e("#login_name"),t=n.parents(".has-feedback"),s=a.find("input[type=submit]");if(a.length){var i=null,o=function(o){i&&clearTimeout(i),i=setTimeout(function(){t.addClass("loading"),t.removeClass("has-success").removeClass("has-error"),e.ajax(n.attr("data-check"),{type:"GET",dataType:"json",data:{login:n.val(),_wpnonce:a.find("input[name=_wpnonce]").val()},success:function(a){a.success?(t.addClass("has-success"),e("#login_nicename").val(a.niceName),s.prop("disabled",!1),o&&o()):(t.addClass("has-error"),s.prop("disabled",!0))},error:function(e){t.addClass("has-error"),s.prop("disabled",!0)},complete:function(){t.removeClass("loading")}})},1500)};n.keyup(function(a){e(this).val().length&&o()}),n.blur(function(a){e(this).val().length&&o()}),a.submit(function(e){e.preventDefault(),n.val().length&&window.confirm("ログイン名を変更します。よろしいですか？")&&o(function(){a.ajaxSubmit({dataType:"json",success:function(e){Hametuha.alert(e.message),setTimeout(function(){window.location.href=e.url},5e3)},error:function(){Hametuha.alert("更新に失敗しました。もう一度やり直してください。",!0)}})})})}e("#select-picture-form, #delete-picture-form").submit(function(a){var n=e("input:checked","#pic-file-list");n.length?e(this).find(".attachment_id_holder").val(n.val()):(a.preventDefault(),Hametuha.alert("画像が選択されていません。"))})})}(jQuery);
//# sourceMappingURL=../map/components/login-change-helper.js.map