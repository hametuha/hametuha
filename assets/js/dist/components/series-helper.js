!function(e){"use strict";e(document).ready(function(){var t=e(".series__list");t.imagesLoaded(function(){t.masonry({itemSelector:".series__item"})}),e('a[href="#series-testimonials-list"]').click(function(t){t.preventDefault(),e(e(this).attr("href")).find(".hidden").removeClass("hidden"),e(this).remove()}),e(".review-creator").on("click",function(t){t.preventDefault();var i=e(this).attr("href"),a=e(this).attr("data-title");Hametuha.modal.open(a,function(t){var a=t.find(".modal-body");a.empty(),e.get(i).done(function(e){a.html(e)}).fail(function(){a.html('<div class="alert alert-danger">レビューを追加できません。</div>')}).always(function(){t.removeClass("loading")})})}),e(document).on("submit","#testimonial-form",function(t){t.preventDefault();var i=e(this),a=i.find("input[type=submit]"),s=function(t){var s=e('<div class="alert alert-danger">'+t+"</div>");i.before(s),a.attr("disabled",!1),setTimeout(function(){s.remove()},3e3)};a.attr("disabled",!0),i.ajaxSubmit({success:function(e){e.success?(i.after('<div class="alert alert-success">'+e.message+"</div>"),i.remove(),setTimeout(Hametuha.modal.close,1500)):s(e.message)},error:function(){s("送信に失敗しました。")}})})})}(jQuery);
//# sourceMappingURL=../map/components/series-helper.js.map