/* 
 * 破滅派サイト全体で共通して読み込まれるファイル
 */

/*global ga: true*/
/*global FB: true*/


(function($){
    window.Hametuha = {
        ga: {
            /**
             * Google Analyticsのイベントを呼ぶ
             *
             * @param {String} category
             * @param {String} action
             * @param {String} label
             * @param {...Number} value
             * @param {...Boolean} nonInteraction
             */
            hitEvent: function(category, action, label, value, nonInteraction){
                try{
                    if( 'undefined' === typeof value){
                       value = 1;
                    }
                    ga('send', {
                        hitType: 'event',
                        eventCategory: category,
                        eventAction: action,
                        eventLabel: label,
                        eventValue: value,
                        nonInteraction: !!nonInteraction
                    });
                }catch(err){}
            },

            /**
             * URLの移動をGAに記録する
             *
             * @param {Event} event
             * @param {String} url
             * @param {String} category
             * @param {String} action
             * @param {String} label
             */
            eventOutbound: function(event, url, category, action, label){
                try{
                    // Send event
                    ga('send', {
                        hitType: 'event',
                        eventCategory: category,
                        eventAction: action,
                        eventLabel: label,
                        hitCallback: function(){
                            window.location.href = url;
                        }
                    });
                    // stopEvent
                    event.preventDefault();
                }catch(err){}
            }
        },

        str: {

            /**
             * 約物開始判定用正規表現
             *
             * @type {RegExp}
             */
            yakumono: /^[ 　【】『』「」（）””""''’’—\-\(\)]/,

            /**
             * 文字列が約物で始まるかどうか
             *
             * @param {String} string
             * @returns {*|boolean}
             */
            startYakumono: function(string){
                return this.yakumono.test(string);
            }
        },

        /**
         * グローバルメッセージを表示する
         *
         * @param {String} message
         * @param {Boolean} error
         */
        alert: function(message, error){
            alert(message);
        },

        /**
         * 投稿が縦書きかどうか
         *
         * @returns {Boolean}
         */
        isTategaki: function(){
            return $('body').hasClass('tategaki');
        },

        /**
         * モデルを格納する名前空間
         * @type {Object}
         */
        models: {},

        /**
         * ビューを格納する名前空間
         */
        views: {},

        /**
         * コレクションを格納する名前空間
         */
        collections: {}
    };
})(jQuery);



jQuery(document).ready(function($){
	// フォームの二重送信防止
	$('form').submit(function(){
		$(this).find('input[type=submit]').attr('disabled', true);
	});
    // 画像のアップロード
    $('.pseudo-uploader').each(function(index, input){
        var file = $(input).next('input');
        $(input).on('click', '.btn', function(e){
            e.preventDefault();
            file.trigger('click');
        });
        file.change(function(e){
            var fileName = $(this).val().split('\\');
            $(input).find('input[type=text]').val(fileName[fileName.length - 1]);
        });
    });
    // フォームのチェックを外す
    $('.form-unlimiter').click(function(e){
        // チェック／アンチェックで送信ボタンを切替
        $(this).parents('form').find('input[type=submit]').prop('disabled', !$(this).attr('checked'));
    });
    // 確認ボタン
    $('a[data-confirm], input[data-confirm]').click(function(e){
        if( !confirm($(this).attr('data-confirm')) ){
            return false;
        }
    });
    // Offcanvas
    $('[data-toggle=offcanvas]').click(function () {
        $('body').toggleClass('offcanvas-on');
    });
    // ツールチップ
    $('.help-tip').tooltip({
        trigger: 'hover focus click',
        container: 'body'
    });
    // シェアボタン
    $(document).on('click', 'a.share', function(e){
        var ga = window.Hametuha.ga,
            brand = $(this).attr('data-medium'),
            href = $(this).attr('href'),
            target = $(this).attr('data-target');
        switch(brand){
            case 'facebook':
                try{
                    FB.ui({
                        method: 'share',
                        href: href
                    }, function(response){
                        if(response){
                            ga.hitEvent('share', brand, target);
                        }
                    });
                }catch(err){}
                break;
            default:
                ga.eventOutbound(e, href, 'share', brand, target);
                break;
        }
    });
    // プロフィールナビ
    var profileNav = $('#profile-navi');
    if( profileNav.length ){
        var profileNavs = {};
        $('section', '#your-profile').each(function(index, section){
            var id = 'profile-section-' + (index + 1);
            $(section).attr('id', id);
            profileNavs[id] = $(section).find('h2, h3:first-child').text();
        });
        for( var id in profileNavs){
            if( profileNavs.hasOwnProperty(id) ){
                profileNav.append('<li><a href="#' + id + '">' + profileNavs[id] + '</a></li>');
            }
        }
    }

    // フォームバリデーション
    $('.validator').submit(function(e){
        $(this).find('runtimer-error').remove();
        var errors = [];
        $(this).find('.required').each(function(index, elt){
            if( !$(elt).val() ){
                $(elt).addClass('errro');
                var label = $('label[for=' + $(elt).attr('id') + ']', this);
                if( label.length ){
                    errors.push( label.text() + 'は必須項目です。');
                }
            }
        });
        if( errors.length ){
            e.preventDefault();
        }
    });
});
