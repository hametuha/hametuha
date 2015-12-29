/* 
 * 破滅派サイト全体で共通して読み込まれるファイル
 */

(function ($) {

    "use strict";

    var Hametuha = window.Hametuha = {
        ga: {
            /**
             * Google Analyticsのイベントを呼ぶ
             *
             * @param {String} category
             * @param {String} action
             * @param {String} label
             * @param {...Number} value Default 1
             * @param {...Boolean} nonInteraction Default false
             */
            hitEvent: function (category, action, label, value, nonInteraction) {
                try {
                    if ('undefined' === typeof value) {
                        value = 1;
                    }
                    ga('send', {
                        hitType       : 'event',
                        eventCategory : category,
                        eventAction   : action,
                        eventLabel    : label,
                        eventValue    : value,
                        nonInteraction: !!nonInteraction
                    });
                } catch (err) {
                }
            },

            /**
             * URLの移動をGAに記録する
             *
             * @param {Event} event
             * @param {String} url
             * @param {String} category
             * @param {String} action
             * @param {String} label
             * @param {...Number} value
             */
            eventOutbound: function (event, url, category, action, label, value) {
                try {
                    if ('undefined' === typeof value) {
                        value = 1;
                    }
                    // Send event
                    ga('send', {
                        hitType      : 'event',
                        eventCategory: category,
                        eventAction  : action,
                        eventLabel   : label,
                        eventValue   : value,
                        hitCallback  : function () {
                            if (Modernizr.touch) {
                                window.location.href = url;
                            } else {
                                if ('share' === category) {
                                    window.open(url, 'outbound', "width=520, height=350");
                                } else {
                                    window.open(url, 'outbound');
                                }
                            }
                        }
                    });
                    // stopEvent
                    event.preventDefault();
                } catch (err) {
                }
            }
        },

        str: {

            /**
             * 約物開始判定用正規表現
             *
             * @type {RegExp}
             */
            yakumono: /^[ 　【】《〔〝『「（”"'’\(\)]/,

            /**
             * 文字列が約物で始まるかどうか
             *
             * @param {String} string
             * @returns {*|boolean}
             */
            startYakumono: function (string) {
                return this.yakumono.test(string);
            }
        },

        /**
         * グローバルメッセージを表示する
         *
         * @param {String} message
         * @param {String} [type]
         */
        alert: function (message, type) {
            var typeName, body, $alert;
            switch( type ){
                case 'info':
                case 'danger':
                case 'warning':
                    typeName = type;
                    break;
                case true: // Backward compats
                case 'error':
                    typeName = 'danger';
                    break;
                default:
                    typeName = 'success';
                    break;
            }
            body = '<div class="alert alert-' + typeName + ' alert-dismissible alert-sticky" role="alert">' +
                    '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                    message +
                '</div>';
            $alert = $(body);
            $('body').append($alert);
            setTimeout(function(){
                $alert.addClass('alert-sticky-on');
            }, 10);
            setTimeout(function(){
                $alert.remove();
            }, 7000);
        },

        /**
         * Show confirm dialog
         *
         * @param {String} message
         * @param {Function} [callback]
         * @param {Boolean} [deletable]
         */
        confirm: function (message, callback, deletable) {
            bootbox.dialog({
                title  : '確認',
                message: message,
                buttons: {
                    cancel: {
                        label    : 'キャンセル',
                        className: 'btn-default'
                    },
                    ok    : {
                        label    : deletable ? '実行' : 'OK',
                        className: deletable ? 'btn-danger' : 'btn-success',
                        callback : callback
                    }
                }
            });
        },

        /**
         * 投稿が縦書きかどうか
         *
         * @returns {Boolean}
         */
        isTategaki: function () {
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
            open : function (title, body) {
                this.reset();
                var $box = $('#hametu-modal');
                $box.find('.modal-title').html(title);
                if (typeof body === 'function') {
                    //
                    $box.addClass('loading');
                    body($box);
                } else {
                    // 追加して開く
                    $box.find('.modal-body').html(body);
                }
                $box.modal('show');
            },
            /**
             * モーダルボックスを閉じる
             */
            close: function () {
                var $box = $('#hametu-modal');
                this.reset();
                $box.modal('hide');
            },

            reset: function (){
                var $box = $('#hametu-modal');
                $box.find('.modal-title').html('');
                $box.find('.modal-body').html('');
            }

        }
    };
})(jQuery);

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

/**
 * アイデアを表示するボタン
 */

/*global Hametuha: true*/
/*global WP_API_Settings: true*/

(function ($) {

    'use strict';

    // ストックボタン
    $(document).on('click', 'a[data-stock]', function (e) {
        var $button = $(this),
            post_id = $button.attr('data-stock');
        e.preventDefault();
        $button.attr('disabled', true);
        $.ajax({
            url       : WP_API_Settings.root + 'hametuha/v1/idea/' + post_id + '/',
            method    : 'POST',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', WP_API_Settings.nonce);
            }
        }).done(function (response) {
            $button.attr('data-stock', null).text('ストック済み');
            Hametuha.alert('このアイデアをストックしました。');
        }).fail(function (response) {
            var message = response.responseJSON ? response.responseJSON.message : '失敗しました。';
            Hametuha.alert(message, true);
            $button.attr('disabled', false);
        });
    });

    // 薦めるボタン
    $(document).on('click', 'a[data-recommend]', function (e) {
        var $button = $(this),
            ideaId  = $button.attr('data-recommend');
        e.preventDefault();
        Hametuha.modal.open('アイデアを薦める', function ($box) {
            // フォームを取得して表示する
            $.post($button.attr('href')).done(function (response) {
                $box.removeClass('loading').find('.modal-body').append(response.html);
            }).fail(function (response) {
                var message = response.responseJSON ? response.responseJSON.message : '失敗しました。';
                Hametuha.alert(message, true);
                Hametuha.modal.close();
            });
        });
    });

    // 薦めるフォーム
    $(document).on('submit', '#recommend-idea-form', function (e) {
        e.preventDefault();
        $.ajax({
            method    : 'PUT',
            url       : WP_API_Settings.root + 'hametuha/v1/idea/' + $(this).attr('data-post-id') + '/',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', WP_API_Settings.nonce);
            },
            data      : {
                user_id: $(this).find("#recommend_to").val()
            }
        }).done(function (response) {
            Hametuha.alert(response.message);
            Hametuha.modal.close();
        }).fail(function (response) {
            var message = response.responseJSON ? response.responseJSON.message : '失敗しました。';
            Hametuha.alert(message, true);
        });
    });

    // アイデアを投稿する
    $(document).on('click', 'a[data-action="post-idea"]', function (e) {
        e.preventDefault();
        var $button = $(this);
        Hametuha.modal.open('アイデアを投稿する', function ($box) {
            $.post($button.attr('href')).done(function (response) {
                $box.removeClass('loading').find('.modal-body').append(response.html);
            }).fail(function (response) {
                var message = response.responseJSON ? response.responseJSON.message : '失敗しました。';
                Hametuha.alert(message, true);
                Hametuha.modal.close();
            });
        });
    });

    // アイデアを保存する
    $(document).on('submit', '#new-idea-form', function (e) {
        e.preventDefault();
        var endpoint     = 'hametuha/v1/idea/mine/',
            $idContainer = $(this).find('#new-idea-id'),
            method, data;
        data = {
            title  : $('#new-idea-name').val(),
            content: $('#new-idea-content').val(),
            status : $('#new-idea-privacy').attr('checked') ? 'private' : 'publish',
            genre  : $('#new-idea-genre').val()
        };
        if ($idContainer.length) {
            method = 'PUT';
            data.post_id = $idContainer.val();
        } else {
            method = 'POST';
        }
        $.ajax({
            method    : method,
            url       : WP_API_Settings.root + endpoint,
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', WP_API_Settings.nonce);
            },
            data      : data
        }).done(function (response) {
            Hametuha.modal.close();
            if (window.location.href === response.url) {
                Hametuha.alert(response.message + '3秒後にページを更新します……');
                setTimeout(function () {
                    window.location.reload();
                }, 3000);
            } else {
                Hametuha.alert(response.message + '<a class="alert-link" href="' + response.url + '">アイデアのページヘ移動する</a>');
            }
        }).fail(function (response) {
            var message = response.responseJSON ? response.responseJSON.message : '失敗しました。';
            Hametuha.alert(message, true);
        });
    });

    // アイデアを削除する
    $(document).on('click', 'a[data-action="delete-idea"]', function (e) {
        e.preventDefault();
        var postId = $(this).attr('data-post-id');
        Hametuha.confirm('このアイデアを削除してよろしいですか？', function () {
            $.ajax({
                method    : 'DELETE',
                url       : WP_API_Settings.root + 'hametuha/v1/idea/mine/?post_id=' + postId,
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', WP_API_Settings.nonce);
                }
            }).done(function (response) {
                Hametuha.alert(response.message);
            }).fail(function (response) {
                var message = response.responseJSON ? response.responseJSON.message : '失敗しました。';
                Hametuha.alert(message, 'danger');
            });
        }, true);
    });

    // アイデアを編集する
    $(document).on('click', 'a[data-action="edit-idea"]', function (e) {
        var endpoint = $(this).attr('href');
        e.preventDefault();
        Hametuha.modal.open('アイデアを編集する', function ($box) {
            $.post(endpoint).done(function (response) {
                $box.removeClass('loading').find('.modal-body').append(response.html);
            }).fail(function (response) {
                var message = response.responseJSON ? response.responseJSON.message : '失敗しました。';
                Hametuha.alert(message, 'danger');
                Hametuha.modal.close();
            });
        });
    });


})(jQuery);

/**
 * リスト作成用のフォーム
 */

/*global Hametuha: true*/

jQuery(document).ready( function ($) {

    'use strict';

    // リスト作成用モーダル
    $(document).on('click', 'a.list-creator', function (e) {
        e.preventDefault();
        var url = $(this).attr('href');
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
        var form = $(this);
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
        var form = $(this);
        form.addClass('loading');
        form.ajaxSubmit({
            success: function (result) {
                form.find('input[type=submit]').attr('disabled', false);
                form.removeClass('loading');
                var msg = $('<div class="alert alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">閉じる</span></button></div>');
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
    var listTpl = $('#my-list-deleter');
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
            var btn = $(this);
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

/**
 * Social related functions
 */

/*global Hametuha: true*/
/*global FB:false*/

jQuery(document).ready(function ($) {

    'use strict';

    // ソーシャルカウント
    $('.row--share').each(function (index, elt) {
        var $box = $(this);
        $.get($box.attr('data-share-url')).done(function (result) {
            if (result.success) {
                for (var brand in result.result) {
                    if (result.result.hasOwnProperty(brand)) {
                        $box.find('a.share--' + brand + ' span').text(result.result[brand]);
                    }
                }
            }
        }).fail(function () {
            // Do nothing
        }).always(function () {
            // Do nothing
        });
    });

    // シェアボタンクリック
    $(document).on('click', 'a.share', function (e) {
        var ga     = window.Hametuha.ga,
            brand  = $(this).attr('data-medium'),
            href   = $(this).attr('href'),
            target = $(this).attr('data-target');
        switch (brand) {
            case 'facebook':
                try {
                    FB.ui({
                        method: 'share',
                        href  : href
                    }, function (response) {
                        if (response) {
                            ga.hitEvent('share', brand, target);
                        }
                    });
                    e.preventDefault();
                } catch (err) {
                }
                break;
            case 'hatena':
                // Do noghing
                break;
            default:
                ga.eventOutbound(e, href, 'share', brand, target);
                break;
        }
    });

    // アウトバウンドを記録
    $(document).on('click', 'a[data-outbound]', function (e) {
        var url      = $(this).attr('href'),
            category = $(this).attr('data-outbound'),
            action   = $(this).attr('data-action'),
            label    = $(this).attr('data-label'),
            value    = $(this).attr('data-value') || 1;
        if (category && action && label) {
            Hametuha.ga.eventOutbound(event, url, category, action, label, value);
        }
    });

    // いいねを集計
    var fbTimer = setInterval(function () {
        if ( window.FB && window.FB.Event ) {
            clearInterval(fbTimer);
            var actions = {
                create: 'like',
                remove: 'dislike'
            };
            $.each(actions, function(prop, action){
                FB.Event.subscribe('edge.' + prop, function (url) {
                    try {
                        ga('send', {
                            hitType      : 'social',
                            socialNetwork: 'facebook',
                            socialAction : action,
                            socialTarget : url.replace(/^https?:\/\/hametuha\.(com|info)/, '')
                        });
                    } catch (err) {
                    }
                });
            });
        }
    }, 100);

    // つぶやきを集計
    var twTimer = setInterval(function () {
        if (window.twttr && window.twttr.events) {
            clearInterval(twTimer);
            $.each( ['follow', 'tweet', 'retweet', 'click', 'favorite'], function(index, key){
                window.twttr.events.bind(key, function (event) {
                    try {
                        ga('send', {
                            hitType      : 'social',
                            socialNetwork: 'twitter',
                            socialAction : key,
                            socialTarget : window.location.pathname
                        });
                    } catch (err) {
                    }
                });
            });
        }
    }, 100);

});

/**
 * Common UI Parts
 */

/*global Hametuha: true*/

jQuery(document).ready(function ($) {

    'use strict';

    // Off canvas
    $('[data-toggle=offcanvas]').click(function () {
        $('body').toggleClass('offcanvas-on');
    });

    // ツールチップ
    $('.help-tip').tooltip({
        trigger  : 'hover focus click',
        container: 'body'
    });

    // プロフィールページのナビ
    var profileNav = $('#profile-navi');
    if (profileNav.length) {
        var profileNavs = {};
        $('section', '#your-profile').each(function (index, section) {
            var id = 'profile-section-' + (index + 1);
            $(section).attr('id', id);
            profileNavs[id] = $(section).find('h2, h3:first-child').text();
        });
        for (var id in profileNavs) {
            if (profileNavs.hasOwnProperty(id)) {
                profileNav.append('<li><a href="#' + id + '">' + profileNavs[id] + '</a></li>');
            }
        }
    }

    $('.subnav__link--toggle').click(function(e){
        var $target = $($(this).attr('data-target'));
        e.preventDefault();
        if( $target.hasClass('toggle') ){
            $('.subnav__child').addClass('toggle');
            $target.removeClass('toggle');
        }else{
            $('.subnav__child').addClass('toggle');
        }
    });

});

/**
 * ユーザーをピックアップするボックス
 */

/*global Hametuha: true*/
/*global WP_API_Settings: true */

(function ($) {

    'use strict';

    /**
     * Get parent container
     *
     * @param {Object} element
     * @returns {jQuery}
     */
    function getParent(element) {
        return $(element).parents('.user-picker');
    }

    /*
     * Avoid Enter
     */
    $(document).on('keydown', '.user-picker__input', function (e) {
        if (e.keyCode == 13) {
            e.preventDefault();
            return false;
        }
    });

    /*
     * Incremental Search
     */
    var userPickerTimer, userPicking = false, tpl = {};
    $(document).on('keyup', '.user-picker__input', function (e) {
        if (userPicking) {
            return;
        }
        // If timer is set, clear.
        if (userPickerTimer) {
            clearTimeout(userPickerTimer);
        }
        var $input     = $(this),
            $container = $input.parents('.user-picker'),
            $lists = $input.next('.user-picker__placeholder'),
            templates  = {};

        userPickerTimer = setTimeout(function () {
            userPicking = true;
            $lists.removeClass('empty').addClass('loading');
            $lists.find('.user-picker__item').each(function(index, li){
                if( ! $(li).find('.user-picker__link.active').length ){
                    $(li).remove();
                }
            });
            $.ajax({
                url       : WP_API_Settings.root + 'hametuha/v1/doujin/following/me/?s=' + $input.val(),
                method    : 'GET',
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', WP_API_Settings.nonce);
                }
            }).done(function (response) {
                if (response.users.length) {
                    var id = $container.attr('data-target');
                    if( ! templates.hasOwnProperty(id) ){
                        templates[id] = $.templates(id + '-template');
                    }
                    $.each(response.users, function (index, user) {
                        $lists.append(templates[id].render(user));
                    });
                }
            }).fail(function (response) {
                var message = response.responseJSON ? response.responseJSON.message : '失敗しました。';
                Hametuha.alert(message, true);
            }).always(function () {
                $lists.removeClass('loading');
                if ( ! $lists.find('.user-picker__item').length ) {
                    $lists.addClass('empty');
                }
                userPickerTimer = null;
                userPicking = false;
            });
        }, 1000);
    });

    $(document).on('click', '.user-picker__link', function (e) {
        e.preventDefault();
        $(this).toggleClass('active');
        var $container = $(this).parents('.user-picker'),
            ids = [],
            max = parseInt($container.attr('data-max'), 10);
        if ($(this).hasClass('active')) {
        } else {
            $(this).parent('li').remove();
            $container.removeClass('filled');
        }
        $container.find('.user-picker__link.active').each(function(index, a){
            ids.push($(a).attr('data-user-id'));
        });
        if (max <= ids.length) {
            $container.addClass('filled');
            $container.find('.user-picker__link:not(.active)').each(function(i, notActive){
                $(notActive).parent('li').remove();
            });
        }
        $($container.attr('data-target')).val(ids.join(','));
    });


})(jQuery);
