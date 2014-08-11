/**
 * 破滅派投稿ページでだけ呼び出されるスクリプト
 */

/*global Hametuha: true*/
/*global postScore: true*/
/*global Chart: true*/
/*global Modernizr: true*/
/*global Backbone: true*/
/*global _: true*/

Chart.defaults.global.responsive = true;


/**
 * シングルポストで読み込む
 */
(function($){
    'use strict';


    Hametuha.views.Scroll = Backbone.View.extend({

        el: '#content-wrapper',

        events: {
        },

        tategaki: false,

        ticker: null,

        slider: null,

        max: 1000,

        timer: 0,

        toLast: false,

        animating: false,

        limit: null,

        initialize: function(){
            _.bindAll(this, 'getSliderPosition', 'updateTicker', 'tickerHandler', 'scrollHandler', 'moveTo', 'finishAnimate', 'passedTime');
            this.ticker = $('<span id="slider-ticker">0%</span>');
            this.tategaki = Hametuha.isTategaki();
            this.slider = $('#slider');
            this.limit = $('#post-author');
            var position = 0;

            this.slider.slider({
                max: this.max,
                min: 0,
                step: 1,
                value: this.getSliderPosition(position),
                slide: this.updateTicker,
                change: this.updateTicker,
                stop: this.tickerHandler
            });
            this.slider.find('.ui-slider-handle').append(this.ticker);

            // スクロールの監視を開始
            $(window).on('resize scroll load', this.scrollHandler);

            // タイマー登録
            var date = new Date();
            this.timer = date.getTime();
        },

        /**
         * 経過秒数を返す
         *
         * @returns {number}
         */
        passedTime: function(){
            var date = new Date();
            return Math.round(( date.getTime() - this.timer) / 1000);
        },


        /**
         * スライドのポジションを取得する
         *
         * @param position
         * @returns {number}
         */
        getSliderPosition: function(position){
            return this.tategaki ? this.max - position : position;
        },

        /**
         * ティッカーの数字を更新
         *
         * @param event
         * @param ui
         */
        updateTicker: function(event, ui){
            if( !isNaN(ui.value) ){
                this.ticker.text(ui.value / 10 + '%');
            }
        },

        /**
         * ティッカーを動かしたときのハンドラ
         *
         * @param event
         * @param ui
         */
        tickerHandler: function(event, ui){
            this.moveTo(ui.value / 1000);
        },

        /**
         * コンテンツを任意の位置に移動
         *
         * @param percent
         */
        moveTo: function(percent){
            var position = ($(document).height() - $(window).height()) * percent;
            this.animating = true;
            $('body, html').animate({ scrollTop: position }, 'fast', this.finishAnimate);
        },

        /**
         * アニメーションが終わったらフラグを折る
         */
        finishAnimate: function(){
            this.animating = false;
        },

        /**
         * スクロールハンドラ
         */
        scrollHandler: function(){
            var scroll = $(window).scrollTop(),
                offset = $(document).height() - $(window).height(),
                percent = Math.min(1, Math.max(0, scroll / offset)),
                limitPos = this.limit.offset().top + 50;
            if( !this.animating ){
                this.slider.slider('value', parseInt(percent * 1000, 10));
            }
            if( !this.toLast && limitPos < scroll + $(window).height() && $('#work-end-ranker').length ){
                this.toLast = true;
                var postId = $('#work-end-ranker').attr('data-post');
                Hametuha.ga.hitEvent('read', 'complete', postId);
                Hametuha.ga.hitEvent('read', 'passed', postId, this.passedTime());
                $('body').addClass('finish-reading');
            }
        }

    });


    jQuery(document).ready(function($){

        // 段落の行頭揃え
        $('.work-content p').each(function(index, elt){
            if( !(Hametuha.str.startYakumono($(elt).text())) && !$(elt).hasClass('wp-caption-text') ){
                $(elt).addClass('indent');
            }
        });

        // ナビゲーション
        var defaultView = 'viewing-content',
            article = $('#viewing-content'),
            nav = $('#footer-single');
        nav.on('click', 'a', function(e){
            e.preventDefault();
            if( $(this).hasClass('active') ){
                $(this).removeClass('active');
                // ビューをデフォルトに戻す
                article.attr('id', defaultView).removeClass('show-nav');
            }else{
                // 全部オフにする
                nav.find('a').removeClass('active');
                $(this).addClass('active');
                var target = $(this).attr('href').replace('#', '');
                if( target.match(/reading-nav/) ){
                    // ビューをデフォルトに戻す
                    article.attr('id', defaultView).addClass('show-nav');
                }else{
                    article.attr('id', 'viewing-' + target.replace('-wrapper','')).removeClass('show-nav');
                }

            }
        });
        $('.reset-viewer').click(function(e){
            e.preventDefault();
            article.attr('id', defaultView).removeClass('show-nav');
            nav.find('a').removeClass('active');
        });


        // スクロール関係をまとめる
        new Hametuha.views.Scroll();

        // レーダーチャート
        var radar = $('#single-radar'), ctx, chart;
        if( radar.length ){
            if( Modernizr.canvas ){
                ctx = radar.get(0).getContext('2d');
                chart = new Chart(ctx).Radar(postScore, {
                });
            }else{
                radar.replaceWith('<p class="alert alert-danger">Canvasに対応していないため、ご利用の環境ではレーダーチャートを表示できません。</p>');
            }
        }

        // 評価送信
        $(document).on('submit', '#review-form', function(e){
            e.preventDefault();
            var btn = $(this).find('input[type=submit]');
            btn.button('loading');
            $(this).ajaxSubmit({
                dataType: 'json',
                success: function(result){
                    btn.button('reset');
                    if( result.error ){
                        Hametuha.alert(result.message, true);
                    }else{
                        Hametuha.alert(result.message);
                        btn.button('complete');
                        if( result.guest ){
                            setTimeout(function(){
                                btn.attr('disabled', true);
                            }, 50);
                        }
                    }
                },
                error: function(){
                    Hametuha.alert('評価を送信できませんでした。', true);
                    btn.button('reset');
                }

            });
        });

        // スターレーティング
        $(document).on('click', '.star-rating i', function(){
            var value = parseInt($(this).attr('data-value'), 10);
            $('.star-rating i').each(function(index, elt){
                if( parseInt($(elt).attr('data-value'), 10) <= value ){
                    $(elt).addClass('active');
                }else{
                    $(elt).removeClass('active');
                }
            });
            $(this).nextAll('input[type=hidden]').val(value);
        });
    });



})(jQuery);