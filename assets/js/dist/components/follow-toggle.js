!function(n){"use strict";n(document).on("click","a.btn-follow",function(o){var t=WP_API_Settings.root+"hametuha/v1/doujin/follow/"+n(this).attr("data-follower-id")+"/",a=n(this),e=n(this).hasClass("btn-following");o.preventDefault(),a.hasClass("btn-follow--loading")||(e?Hametuha.confirm("フォローを解除してよろしいですか？",function(){a.addClass("btn-follow--loading"),n.ajax({url:t,method:"DELETE",beforeSend:function(n){n.setRequestHeader("X-WP-Nonce",WP_API_Settings.nonce)},data:{}}).done(function(n){a.removeClass("btn-following")}).fail(function(){Hametuha.alert("フォローを解除できませんでした",!0)}).always(function(){a.removeClass("btn-follow--loading")})},!0):(a.addClass("btn-follow--loading"),n.ajax({url:t,method:"POST",beforeSend:function(n){n.setRequestHeader("X-WP-Nonce",WP_API_Settings.nonce)},data:{}}).done(function(n){a.addClass("btn-following")}).fail(function(){Hametuha.alert("フォローに失敗しました。すでにフォロー済みか、サーバが混み合っています。",!0)}).always(function(){a.removeClass("btn-follow--loading")})))})}(jQuery);
//# sourceMappingURL=../map/components/follow-toggle.js.map