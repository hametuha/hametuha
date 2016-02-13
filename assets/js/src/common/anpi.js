/**
 * 安否投稿フォーム
 */

/*global Hametuha: false*/
/*global wpApiSettings: true*/

(function ($) {
    'use strict';

    $(document).on('click', '.anpi-new', function(e){
        e.preventDefault();
        Hametuha.modal.open('安否報告', function($box){
            $.post('/anpi/mine/new/').done(function (response) {
                $box.removeClass('loading').find('.modal-body').append(response.html);

            }).fail(function (response) {
                var message = response.responseJSON ? response.responseJSON.message : '失敗しました。';
                Hametuha.alert(message, true);
                Hametuha.modal.close();
            });
        });
    });

    $(document).on('submit', '#new-tweet-form', function(e){
        e.preventDefault();
        var $id = $(this).find('#new-tweet-id'),
            method, data;
        data = {
            content: $.trim($(this).find('#new-anpi-content').val())
        };
        if( ! data.content.length ){
            return;
        }
        if ( $id.length ) {
            // Edit
            method = 'PUT';
        } else {
            // Newly create
            method = 'POST';
            data.mention = $(this).find('#mention').val();
        }
        $.ajax({
            method: method,
            url: wpApiSettings.root + 'hametuha/v1/anpi/new/',
            beforeSend: function( xhr ){
                xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
            },
            data: data
        }).done(function(response){
            Hametuha.alert('めつかれさまでした。安否報告を受け付けました。');
        }).fail(function(response){
            var message = response.responseJSON ? response.responseJSON.message : '失敗しました。';
            Hametuha.alert(message, true);
        }).always(function(response){
            Hametuha.modal.close();
        });

    });

})(jQuery);
