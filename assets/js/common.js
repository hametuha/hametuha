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
        collections: {},

        /**
         * モーダル関係
         */
        modal: {
            /**
             * モーダルボックスを表示する
             *
             * @param {String} title
             * @param {String|Function} body
             */
            open: function(title, body){
                var box = $('#hametu-modal');
                box.find('.modal-title').html(title);
                if( typeof body === 'function'){
                    //
                    box.addClass('loading');
                    body(box);
                }else{
                    // 追加して開く
                    box.find('.modal-body').html(body);
                }
                box.modal('show');
            },
            /**
             * モーダルボックスを閉じる
             */
            close: function(){
                var box = $('#hametu-modal');
                box.find('.modal-title').html('');
                box.find('.modal-body').html('');
                box.modal('hide');
            }
        }
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

    // Off canvas
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
        $(this).find('runtime-error').remove();
        var errors = [];
        $(this).find('.required').each(function(index, elt){
            if( !$(elt).val() ){
                $(elt).addClass('erro');
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

    // リスト作成用モーダル
    $(document).on('click', 'a.list-creator', function(e){
        e.preventDefault();
        var url = $(this).attr('href');
        Hametuha.modal.open($(this).attr('title'), function(box){
            $.get(url, {}, function(result){
                box.removeClass('loading');
                box.find('.modal-body').html(result.html);
            });
        });
    });

    // リスト作成フォーム
    $(document).on('submit', '.list-create-form', function(e){
        e.preventDefault();
        var form = $(this);
        form.addClass('loading');
        form.ajaxSubmit({
            success: function(result){
                if( result.success ){
                    form.trigger('created.hametuha', [result.post]);
                }else{
                    alert(result.message);
                }
                form.find('input[type=submit]').attr('disabled', false);
                form.removeClass('loading');
            }
        });
    });

    // モーダルの中のリスト
    $('.modal').on('created.hametuha', '.list-create-form', function(e, post){
        Hametuha.modal.close();
        Hametuha.ga.hitEvent('list', 'add', post.ID);
        if( $('body').hasClass('single-lists') ){
            location.reload();
        }
    });

    // リスト追加フォーム
    $(document).on('submit', '.list-save-manager', function(e){
        e.preventDefault();
        var form = $(this);
        form.addClass('loading');
        form.ajaxSubmit({
            success: function(result){
                form.find('input[type=submit]').attr('disabled', false);
                form.removeClass('loading');
                var msg = $('<div class="alert alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">閉じる</span></button></div>');
                msg.addClass('alert-' + ( result.success ? 'success' : 'danger' ))
                    .append('<span>' + result.message + '</span>');
                form.prepend(msg);
                setTimeout(function(){
                    msg.find('button').trigger('click');
                }, 5000);
            }
        });
    });

    // リスト削除ボタン
    $(document).on('click', '.list-eraser', function(e){
        e.preventDefault();
        if( confirm($(this).attr('title')) ){
            $.post($(this).attr('href'), function(result){
                alert(result.message);
                if( result.success && $('body').hasClass('single-lists') ){
                    window.location.href = result.url;
                }
            });
        }
    });

    // リストから投稿を削除
    var listTpl = $('#my-list-deleter');
    if( listTpl.length ){
        // ボタンを追加
        $('ol.media-list > li').each(function(index, elt){
            $(elt).find('.list-inline').append(listTpl.render({
                postId: $(elt).attr('data-post-id')
            }));
        });
        // イベントリスナー
        $('ol.media-list').on('click', '.deregister-button', function(e){
            e.preventDefault();
            var btn = $(this);
            if( confirm('リストからこの作品を削除します。この操作は取り消せませんが、よろしいですか？') ){
                $.post(btn.attr('href'), {}, function(result){
                    if( result.success ){
                        btn.parents('li.media').remove();
                        if( !$('ol.media-list > li').length ){
                            $('ol.media-list').before('<div class="alert alert-danger">' + result.message + '</div>');
                            setTimeout(function(){
                                window.location.href = result.home_url;
                            }, 3000);
                        }
                    }else{
                        alert(result.message);
                    }
                });
            }
        });
    }


});
