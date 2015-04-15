/**
 * Description
 */

/*global Hametuha: true*/

(function ($) {
    'use strict';


    $(document).ready(function(){
        var form = $('#become-author-form'), cb, submit;

        if( form.length ){
            submit = form.find('input[type=submit]');
            // 送信
            form.submit(function(e){
                cb = form.find('input[name=review_contract]:checked');
                e.preventDefault();
                if( cb.length ){
                    form.ajaxSubmit({
                        dataType: 'json',
                        success: function(result){
                            Hametuha.alert(result.message, !result.success);
                            if( result.success ){
                                setTimeout(function(){
                                    window.location.href = result.url;
                                }, 5000);
                            }else if( cb.length ){
                                submit.prop('disabled', false);
                            }
                        },
                        error: function(){
                            Hametuha.alert('更新に失敗しました。時間を置いてからもう一度やり直してください。', true);
                        }
                    });
                }else{
                    Hametuha.alert('利用規約に同意されていません。', true);
                }
            });
        }

    });

})(jQuery);
