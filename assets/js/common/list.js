/*!
 * リスト作成用のフォーム
 *
 * @handle hametuha-list
 * @deps jquery
 */

jQuery(document).ready( function ($) {

    'use strict';

    // リスト作成用モーダル
    $(document).on('click', 'a.list-creator', function (e) {
        e.preventDefault();
        const url = $(this).attr('href');
        Hametuha.modal.open($(this).attr('title'), function (box) {
            $.get(url, {}, function (result) {
                box.removeClass('loading');
                box.find('.modal-body').html(result.html);
            });
        });
    });

    // リスト作成フォーム
    $(document).on('submit', '.list-create-form', function (e) {
        e.preventDefault();
        const form = $(this);
        form.addClass('loading');
        form.ajaxSubmit({
            success: function (result) {
                if (result.success) {
                    form.trigger('created.hametuha', [result.post]);
                } else {
                    Hametuha.alert(result.message, true);
                }
                form.find('input[type=submit]').attr('disabled', false);
                form.removeClass('loading');
            }
        });
    });

    // モーダルの中のリスト
    $('.modal').on('created.hametuha', '.list-create-form', function (e, post) {
        Hametuha.modal.close();
        Hametuha.ga.hitEvent('list', 'add', post.ID);
        if ($('body').hasClass('single-lists')) {
            location.reload();
        }
    });

    // リスト追加フォーム
    $(document).on('submit', '.list-save-manager', function (e) {
        e.preventDefault();
        const form = $(this);
        form.addClass('loading');
        form.ajaxSubmit({
            success: function (result) {
                form.find('input[type=submit]').attr('disabled', false);
                form.removeClass('loading');
                const msg = $('<div class="alert alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">閉じる</span></button></div>');
                msg.addClass('alert-' + ( result.success ? 'success' : 'danger' ))
                    .append('<span>' + result.message + '</span>');
                form.prepend(msg);
                setTimeout(function () {
                    msg.find('button').trigger('click');
                }, 5000);
            }
        });
    });

    // リスト削除ボタン
    $(document).on('click', '.list-eraser', function (e) {
        e.preventDefault();
        Hametuha.confirm($(this).attr('title'), function() {
            $.post($(this).attr('href'), function (result) {
                Hametuha.alert(result.message);
                if (result.success && $('body').hasClass('single-lists')) {
                    window.location.href = result.url;
                }
            });
        }, true);
    });

    // リストから投稿を削除
    const listTpl = $('#my-list-deleter');
    if (listTpl.length) {
        // ボタンを追加
        $('ol.media-list > li').each(function (index, elt) {
            $(elt).find('.list-inline').append(listTpl.render({
                postId: $(elt).attr('data-post-id')
            }));
        });
        // イベントリスナー
        $('ol.media-list').on('click', '.deregister-button', function (e) {
            e.preventDefault();
            const btn = $(this);
            Hametuha.confirm('リストからこの作品を削除します。この操作は取り消せませんが、よろしいですか？', function(){
                $.post(btn.attr('href'), {}, function (result) {
                    if (result.success) {
                        btn.parents('li.media').remove();
                        if (!$('ol.media-list > li').length) {
                            $('ol.media-list').before('<div class="alert alert-danger">' + result.message + '</div>');
                            setTimeout(function () {
                                window.location.href = result.home_url;
                            }, 3000);
                        }
                    } else {
                        Hametuha.alert(result.message, true);
                    }
                });
            }, true);
        });
    }


});
