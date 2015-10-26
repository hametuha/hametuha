/**
 * Description
 */

/*global wp: true*/

(function ($) {
    'use strict';

    $(document).ready(function(){

        var haMediaFrame, // メディアフレーム
            $picker = $('.image-picker', '#wpbody-content'), // プロフィール
            $img = $picker.find('.new-img'); // プレースホルダー

        // 画像の指定
        $picker.on('click', '.button-primary', function(e){
            // デフォルトイベントの抑止
            e.preventDefault();

            if( !haMediaFrame ){
                // はじめてなので、メディアフレームを初期化
                haMediaFrame = wp.media.frames.haMediaFrame = wp.media({
                    className: 'media-frame ha-media-frame',
                    frame: 'select',
                    multiple: false,
                    title: '使用する画像をアップロードまたは選択してください。',
                    library: {
                        type: 'image'
                    },
                    button: {
                        text: '選択した画像を選ぶ'
                    }
                });
                // 選択した場合のイベントをバインド
                haMediaFrame.on('select', function(){
                    // アタッチメントの情報を取得
                    var attachment = haMediaFrame.state().get('selection').first().toJSON(),
                        src;
                    // 画像のSRCを取得&設定。小さいサイズがあれば取得し、なければフルサイズ
                    // pinkyは add_image_size で追加したオリジナルサイズ
                    src = attachment.sizes.pinky ? attachment.sizes.pinky.url : attachment.sizes.full.url;
                    $img.attr('src', src);
                    $picker.find('input').val(attachment.id);
                    $picker.find('p').effect('highlight');
                });
            }
            haMediaFrame.open();
        });

        // 画像の解除
        $picker.on('click', '.button', function(e){
            e.preventDefault();
            $img.attr('src', $img.attr('data-src'));
            $picker.find('input').val('');
            $picker.find('p').effect('highlight');
        });


    });

})(jQuery);
