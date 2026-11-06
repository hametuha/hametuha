/**
 * bodyタグの開始直後に読み込まれる
 */

/*global FB: false*/
/*global twttr: false*/
/*global Hametuha: true*/
/*global HametuhaSocial: false */

(function ($) {

  "use strict";

  //
  // アウトバウンドを記録
  // -------------------
  //
  $(document).on('click', 'a[data-outbound]', function (e) {
    var url      = $(this).attr('href'),
        category = $(this).attr('data-outbound'),
        action   = $(this).attr('data-action'),
        label    = $(this).attr('data-label'),
        value    = $(this).attr('data-value') || 1;
    if (category && action && label) {
      Hametuha.ga.eventOutbound( e, url, category, action, label, value );
    }
  });


  //
  // Facebook
  // ------------------
  //
  window.fbAsyncInit = function () {
    // 初期化
    FB.init({
      appId  : '196054397143922',
      xfbml  : true,
      autoLogAppEvents : true,
      version: 'v3.2'
    });
    // イベントを監視
    var actions = {
      'comment.create': 'comment',
      'comment.remove': 'uncomment',
      'edge.create': 'like',
      'edge.remove': 'dislike',
      'message.send': 'message'
    };
    $.each(actions, function (prop, action) {
      FB.Event.subscribe( prop, function (url) {
        var href = url.hasOwnProperty('href') ? url.href : url;
        try {
          gtag( 'event', 'share', {
            method: 'facebook',
            content_type: 'article',
            item_id: href.replace( /^https?:\/\/hametuha\.(com|info)/, '' )
          });
        } catch (err) {
        }
      });
    });
  };
  (function (d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
    js = d.createElement(s);
    js.id = id;
    js.async = true;
    js.src = HametuhaSocial.needChat ? '//connect.facebook.net/ja_JP/sdk/xfbml.customerchat.js' : "//connect.facebook.net/ja_JP/sdk.js";
    fjs.parentNode.insertBefore(js, fjs);
  }(document, 'script', 'facebook-jssdk'));



  //
  // Twitter
  // ---------------
  //
  window.twttr = (function (d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0],
        t       = window.twttr || {};
    if (d.getElementById(id)) return;
    js = d.createElement(s);
    js.id = id;
    js.src = "https://platform.twitter.com/widgets.js";
    js.async = true;
    fjs.parentNode.insertBefore(js, fjs);

    t._e = [];
    t.ready = function (f) {
      t._e.push(f);
    };

    return t;
  }(document, "script", "twitter-wjs"));

  // つぶやきを集計
  twttr.ready(function (twttr) {
    $.each(['follow', 'tweet', 'retweet', 'click', 'favorite'], function (index, key) {
      twttr.events.bind(key, function (event) {
        try {
          gtag( 'event', 'share', {
            method: 'twitter',
            content_type: 'article',
            item_id: window.location.pathname
          });
        } catch (err) {
          // Do nothing
        }
      });
    });
  });

})(jQuery);
