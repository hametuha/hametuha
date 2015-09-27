/**
 * Description
 */

/*global Modernizr: true*/
/*global Hametuha: true */
/*global HametuhaGenreStatic: true*/

(function ($) {
    'use strict';


    $(document).ready(function(){

        // masonry
        var $container = $('.series__list');
        $container.imagesLoaded( function() {
            $container.masonry({
                itemSelector: '.series__item'
            });
        });

        // more
        $('a[href="#series-testimonials-list"]').click(function(e){
            e.preventDefault();
            $($(this).attr('href')).find('.hidden').removeClass('hidden');
            $(this).remove();
        });

        // レビュー追加
        $('.review-creator').on('click', function( e ){
            e.preventDefault();
            var url = $(this).attr('href'),
                title = $(this).attr('data-title');
            Hametuha.modal.open(title, function(box){
                var $body = box.find('.modal-body');
                $body.empty();
                $.get(url).done(function(result){
                    $body.html(result);
                }).fail(function(){
                    $body.html('<div class="alert alert-danger">レビューを追加できません。</div>');
                }).always(function(){
                    box.removeClass('loading');
                });
            });
        });

        // レビュー送信
        $(document).on('submit', '#testimonial-form', function(e){
            e.preventDefault();
            var $form = $(this),
                $button = $form.find('input[type=submit]'),
                errorHandler = function(msg){
                    var $error = $('<div class="alert alert-danger">' + msg + '</div>');
                    $form.before($error);
                    $button.attr('disabled', false);
                    setTimeout(function(){
                        $error.remove();
                    }, 3000);
                };
            $button.attr('disabled', true);
            $form.ajaxSubmit({
                success: function( result ){
                    if( result.success ){
                        $form.after('<div class="alert alert-success">' + result.message + '</div>');
                        $form.remove();
                        setTimeout(Hametuha.modal.close, 1500);
                    }else{
                        errorHandler(result.message);
                    }
                },
                error: function(){
                    errorHandler('送信に失敗しました。');
                }
            });
        });

    });

})(jQuery);
