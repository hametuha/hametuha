/**
 * Description
 */

/*global WP_API_Settings: true*/

(function ($) {

    "use strict";

    // フォロー・アンフォロー
    $(document).on('click', 'a.btn-follow', function(e){
        var endpoint = WP_API_Settings.root + 'hametuha/v1/doujin/follow/' + $(this).attr('data-follower-id') + '/',
            $btn = $(this),
            following = $(this).hasClass('btn-following');
        e.preventDefault();
        if( $btn.hasClass('btn-follow--loading') ){
            return;
        }
        if( following ){
            Hametuha.confirm('フォローを解除してよろしいですか？', function(){
                $btn.addClass('btn-follow--loading');
                $.ajax( {
                    url: endpoint,
                    method: 'DELETE',
                    beforeSend: function ( xhr ) {
                        xhr.setRequestHeader( 'X-WP-Nonce', WP_API_Settings.nonce );
                    },
                    data:{}
                } ).done( function ( response ) {
                    $btn.removeClass('btn-following');
                }).fail(function(){
                    Hametuha.alert('フォローを解除できませんでした', true);
                }).always(function(){
                   $btn.removeClass('btn-follow--loading');
                });
            }, true);
        }else{
            $btn.addClass('btn-follow--loading');
            $.ajax( {
                url: endpoint,
                method: 'POST',
                beforeSend: function ( xhr ) {
                    xhr.setRequestHeader( 'X-WP-Nonce', WP_API_Settings.nonce );
                },
                data:{}
            } ).done( function ( response ) {
                $btn.addClass('btn-following');
            }).fail(function(){
               Hametuha.alert('フォローに失敗しました。すでにフォロー済みか、サーバが混み合っています。', true);
            }).always(function(){
                $btn.removeClass('btn-follow--loading');
            });
        }

    });


})(jQuery);
