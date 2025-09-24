/**
 * アイデアを表示するボタン
 */

/*global Hametuha: true*/
/*global wpApiSettings: true*/

(function ($) {

    'use strict';



    // 薦めるボタン
    $(document).on('click', 'a[data-recommend]', function (e) {
        var $button = $(this),
            ideaId  = $button.attr('data-recommend');
        e.preventDefault();
        Hametuha.modal.open('アイデアを薦める', function ($box) {
            // フォームを取得して表示する
            $.post($button.attr('href')).done(function (response) {
                $box.removeClass('loading').find('.modal-body').append(response.html);
            }).fail(function (response) {
                var message = response.responseJSON ? response.responseJSON.message : '失敗しました。';
                Hametuha.alert(message, true);
                Hametuha.modal.close();
            });
        });
    });

    // 薦めるフォーム
    $(document).on('submit', '#recommend-idea-form', function (e) {
        e.preventDefault();
        $.ajax({
            method    : 'PUT',
            url       : wpApiSettings.root + 'hametuha/v1/idea/' + $(this).attr('data-post-id') + '/',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
            },
            data      : {
                user_id: $(this).find("#recommend_to").val()
            }
        }).done(function (response) {
            Hametuha.alert(response.message);
            Hametuha.modal.close();
        }).fail(function (response) {
            var message = response.responseJSON ? response.responseJSON.message : '失敗しました。';
            Hametuha.alert(message, true);
        });
    });
})(jQuery);
