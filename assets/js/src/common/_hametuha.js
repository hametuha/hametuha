/* 
 * 破滅派サイト全体で共通して読み込まれるファイル
 */

/*global HametuhaGlobal:false */

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
         * @param {Number} [delay]
         */
        alert: function (message, type, delay) {
            var typeName, body, $alert;
            if( undefined === delay ){
                delay = 7000;
            }
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
            }, delay);
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
         * Angularのtemplateを返す
         *
         * @param templateName
         * @returns {*}
         */
        template: function(templateName){
            return HametuhaGlobal.angularTemplateDir + templateName;
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
