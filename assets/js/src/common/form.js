/**
 * フォームの処理を行う
 */

jQuery(document).ready(function ($) {

    "use strict";

    var Hametuha = window.Hametuha;

    // フォームの二重送信防止
    $('form').submit(function () {
        $(this).find('input[type=submit]').attr('disabled', true);
    });

    // 画像のアップロード
    $('.pseudo-uploader').each(function (index, input) {
        var file = $(input).next('input');
        $(input).on('click', '.btn', function (e) {
            e.preventDefault();
            file.trigger('click');
        });
        file.change(function (e) {
            var fileName = $(this).val().split('\\');
            $(input).find('input[type=text]').val(fileName[fileName.length - 1]);
        });
    });

    // フォームのチェックを外す
    $('.form-unlimiter').click(function (e) {
        // チェック／アンチェックで送信ボタンを切替
        $(this).parents('form').find('input[type=submit]').prop('disabled', !$(this).attr('checked'));
    });

    // 確認ボタン
    $('a[data-confirm], input[data-confirm]').click(function (e) {
        if (!(window.confirm($(this).attr('data-confirm')))) {
            return false;
        }
    });

    // フォームバリデーション
    $('.validator').submit(function (e) {
        $(this).find('runtime-error').remove();
        var errors = [];
        $(this).find('.required').each(function (index, elt) {
            if (!$(elt).val()) {
                $(elt).addClass('erro');
                var label = $('label[for=' + $(elt).attr('id') + ']', this);
                if (label.length) {
                    errors.push(label.text() + 'は必須項目です。');
                }
            }
        });
        if (errors.length) {
            e.preventDefault();
        }
    });

    // 検索フォーム
    $(document).on('submit', '#searchBox form', function(){
        var $checked = $(this).find('input[name=post_type]:checked');
        switch( $checked.val() ){
            case 'any':
                // Remove radio
                $checked.attr('checked', false);
                break;
            case 'author':
                $(this).attr('action', $checked.attr('data-search-action'));
                $checked.attr('checked', false);
                break;
            default:
                // Do nothing
                break;
        }
    });
});
