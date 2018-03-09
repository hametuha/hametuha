/**
 * Description
 */
!function(t){"use strict";t(document).ready(function(){function n(){var n=0,o=parseInt(a.attr("data-last-checked"),10);e.find("li[data-time]").each(function(e,a){n=Math.max(n,parseInt(t(a).attr("data-time"),10))}),o>n?a.removeClass("has-notify"):a.addClass("has-notify")}var e=t("#notification-link"),a=e.find("a.dropdown-toggle");e.length&&(n(),setInterval(function(){t.get(HametuhaNotification.retrieve,{_wpnonce:HametuhaNotification.nonce}).done(function(a){if(a.length){var o=e.find(".divider");o.prevAll("li").remove();for(var i=0,c=a.length;i<c;i++)o.before(t(a[i]))}n()})},15e3),e.on("show.bs.dropdown",function(){t.post(HametuhaNotification.endpoint,{_wpnonce:HametuhaNotification.nonce}).done(function(t){a.attr("data-last-checked",t.checked),n()})}))})}(jQuery);
//# sourceMappingURL=../map/components/notification.js.map
