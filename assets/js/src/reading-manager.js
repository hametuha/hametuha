/**
 * 破滅派の読み込みを司るクラス
 */

/* global HametuhaVars:true */
/* global swfobject:true */




(function($){

    "use strict";

    var hametuhaReadingManager = window.HametuhaReadingManager = {

        alert: function(string){
            window.alert(string);
        },

        bookmarks:{

        },

        tweet: function(string){
            var link = jQuery('link[rel=canonical]').attr('href');
            var twitterBaseUri = 'http://twitter.com/home?status=';
            var tweetURI = twitterBaseUri + encodeURIComponent('QT: ' + string + ' ' + link + ' ' + '#破滅派');
            window.open(tweetURI, 'twitter', 'width=600, height=400, menubar=no, toolbar=no, scrollbars=yes').focus();
        },

        saveFrase: function(postID, frase, location){
            jQuery.post(
                HametuhaVars.endpoint,
                {
                    action: HametuhaVars.addFavorite,
                    _wpnonce: HametuhaVars.nonce,
                    post_id: postID,
                    location: location,
                    frase: frase
                },
                function(result){
                    var message = result.status ? 'テキストをお気に入りに登録しました。': 'ごめんなさい、エラーが起きたためお気に入りに登録できませんでした。';
                    hametuhaReadingManager.getContainer().showNotification(message);
                }
            );
        },

        addReview: function(postID){
            jQuery.fancybox.open(
                {
                    href: HametuhaVars.feedbackURL + '&post_id=' + postID,
                    title: '作品を評価する'
                },
                {
                    type: 'iframe',
                    width: 700
                }
            );
        },

        isOpen: function(){
            return jQuery("#reading-container").css('left') != '-100%';
        },

        toggleMetaPanel: function(e){
            if(e){
                e.preventDefault();
            }
            var prop;
            switch(jQuery(this).attr('id')){
                case 'meta-drawer':
                    prop = '-100%';
                    break;
                default:
                    prop = 0;
                    break;
            }
            jQuery('#reading-container').css({
                left: prop
            });
        },

        search: function(str){
            this.getContainer().searchContent(str);
        },

        getContainer: function(){
            if (navigator.appName.indexOf("Microsoft") != -1) {
                 return window.reader;
             } else {
                 return document.reader;
             }
        },

        fitHeight: function(){
            var winHeight = jQuery('body').hasClass('mobile') ? window.innerHeight : jQuery(window).height();
            jQuery('#reading-container').css('height', winHeight - (jQuery('#wpadminbar').height() + jQuery('#post-feedback').height()) );
            if(jQuery('body').hasClass('mobile')){
                jQuery('#reading-container .inner').css('height',jQuery('#reading-container').height() - 100);
                //jQuery('body').css('minHeight', window.innerHeight + 1);
            }
        },

        embed: function(){
            swfobject.embedSWF(
                HametuhaVars.swf,
                'reader',
                '100%',
                '100%',
                '10.2.0',
                HametuhaVars.expressInstall,
                {
                    endpoint: HametuhaVars.endpoint,
                    userID: HametuhaVars.userID,
                    postID: HametuhaVars.postID,
                    getContent: HametuhaVars.getContent,
                    postTitle: HametuhaVars.postTitle,
                    postAuthor: HametuhaVars.postAuthor,
                    fontURL: HametuhaVars.fontSwf,
                    isPreview: HametuhaVars.isPreview
                },
                {
                    wmode: 'transparent',
                    quality: 'AUTOHIGH',
                    allowScriptAccess: 'always',
                    allowFullScreen: true
                }
            );
        },

        width: 0,

        curPage: 1,

        totalPage: 0,

        pageWidth: 0,

        startTime: 0,

        startX: 0,

        latestX: 0,



        setupPagenation: function(){
            this.curPage = 1;
            this.width = $('.post-content', '#reading-container').width();
            this.pageWidth = $('.inner', '#reading-container').width();
            this.totalPage = $('.post-content', '#reading-container').width() / this.pageWidth;
        },

        movePos: function (page){
            page = Math.max(1, page);
            $('.inner', '#reading-container').scrollLeft(this.width - (page - 1) * this.pageWidth);
            this.curPage = page;
        },

        nextPage: function (){
            this.curPage++;
            this.movePos(this.curPage);
        },

        prevPage: function (){
            this.curPage--;
            if(this.curPage < 1){
                this.curPage = 1;
            }else{
                this.movePos(this.curPage);
            }
        },

        touchStart: function(e){
            hametuhaReadingManager.startTime = (new Date()).getTime();
            hametuhaReadingManager.startX = hametuhaReadingManager.latestX = e.originalEvent.touches[0].pageX;
            jQuery('#reading-container .inner').bind('touchmove', hametuhaReadingManager.touchMove);
        },

        touchMove: function(e){
            hametuhaReadingManager.latestX = e.originalEvent.touches[0].pageX;
        },

        touchEnd: function(e){
            $('#reading-container .inner').unbind('touchmove', hametuhaReadingManager.touchMove);
            var moved = hametuhaReadingManager.latestX - hametuhaReadingManager.startX,
                passed = (new Date()).getTime() - hametuhaReadingManager.startTime;
            if(passed > 1999){
                //2秒以上なら関係ない
                return;
            }
            if(Math.abs(moved) < window.innerWidth / 2 ){
                //画面の半分以上フリックしてない
                return;
            }
            /*
            if(moved < 0){
                hametuhaReadingManager.prevPage();
            }else{
                hametuhaReadingManager.nextPage();
            }
            //スクロールする
            hametuhaReadingManager.startTime = 0;
            hametuhaReadingManager.startX = 0;
            hametuhaReadingManager.latestX = 0;
            */
        }
    };
})(jQuery);

//DomReady
jQuery(document).ready(function($){

    "use strict";

    var hametuhaReadingManager = window.HametuhaReadingManager;

	if($('#reading-container').length > 0){
		//元コンテンツを削除
		var content = $('.single-post-content').remove();
		$('#meta-drawer, #meta-opener').click(hametuhaReadingManager.toggleMetaPanel);
		//検索ボックス
		$('#search').focus(function(e){
			$(this).parents('form').addClass('active');
		});
		$('#search').blur(function(e){
			$(this).parents('form').removeClass('active');
		});
		$('#search-box').submit(function(e){
			e.preventDefault();
			var searchStr = $('#search').val();
			if(searchStr.length > 0){
				hametuhaReadingManager.search(searchStr);
			}
		});
		//カウントダウン開始
		var timer = setInterval(function(){
			var rest = parseInt($('#reader-open-indicator strong').text()) - 1;
			if(rest < 0){
				$('#reader-open-indicator').remove();
				clearInterval(timer);
				if(!hametuhaReadingManager.isOpen()){
					hametuhaReadingManager.toggleMetaPanel(false);
				}
				return;
			}
			$('#reader-open-indicator strong').text(rest);
		}, 1000);
		//Flash用コンテナのサイズを調整
		hametuhaReadingManager.fitHeight();
		//リサイズされた場合
		jQuery(window).resize(function(e){
			hametuhaReadingManager.fitHeight();
		});
		//エンベッド
		if($('body').hasClass('mobile')){
			$('#reading-container .inner').append(content);
			setTimeout(function(){
				window.scrollTo(0, 1);
				hametuhaReadingManager.fitHeight();
			}, 100);
			$(window).scroll(hametuhaReadingManager.fitHeight);
			hametuhaReadingManager.setupPagenation();
			hametuhaReadingManager.movePos(1);
		}else{
			hametuhaReadingManager.embed();
		}
	}
});

