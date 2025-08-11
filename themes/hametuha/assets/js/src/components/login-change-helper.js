/**
 * Description
 */

/*global Hametuha: true*/

(function ($) {
    'use strict';

    $(document).ready(function(){
        var form = $('#change-login-form'),
            input = $('#login_name'),
            container = input.parents('.has-feedback'),
            submit = form.find('input[type=submit]');

        if( form.length ){
            var timer = null,
                checkLogin = function(callback){
                    // リセットする
                    if( timer ){
                        clearTimeout(timer);
                    }
                    timer = setTimeout(function(){
                        container.addClass('loading');
                        container.removeClass('has-success').removeClass('has-error');
                        $.ajax(input.attr('data-check'), {
                            type: 'GET',
                            dataType: 'json',
                            data: {
                                login: input.val(),
                                _wpnonce: form.find('input[name=_wpnonce]').val()
                            },
                            success: function(result){
                                if( result.success ){
                                    container.addClass('has-success');
                                    $('#login_nicename').val(result.niceName);
                                    submit.prop('disabled', false);
                                    if( callback ){
                                        callback();
                                    }
                                }else{
                                    container.addClass('has-error');
                                    submit.prop('disabled', true);
                                }
                            },
                            error: function(error){
                                container.addClass('has-error');
                                submit.prop('disabled', true);
                            },
                            complete: function(){
                                container.removeClass('loading');
                            }
                        });
                    }, 1500);
                };

            // キータイプで検索
            input.keyup(function(e){
                if( $(this).val().length ){
                    checkLogin();
                }
            });
            // フォーカスが外れたら検索
            input.blur(function(e){
                if( $(this).val().length ){
                    checkLogin();
                }
            });

            // 送信
            form.submit(function(e){
                e.preventDefault();
                if( input.val().length && window.confirm('ログイン名を変更します。よろしいですか？') ){
                    checkLogin(function(){
                        form.ajaxSubmit({
                            dataType: 'json',
                            success: function(result){
                                Hametuha.alert(result.message, false);
                                setTimeout(function(){
                                    window.location.href = result.url;
                                }, 5000);
                            },
                            error: function(){
                                Hametuha.alert('更新に失敗しました。もう一度やり直してください。', true);
                            }
                        });
                    });
                }
            });
        }


        // プロフィール写真変更フォーム
        $('#select-picture-form, #delete-picture-form').submit(function(e){
            var $checked = $('input:checked', '#pic-file-list');
            if( !$checked.length ){
                e.preventDefault();
                Hametuha.alert('画像が選択されていません。');
            }else{
                $(this).find('.attachment_id_holder').val($checked.val());
            }
        });

    });

})(jQuery);
