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
