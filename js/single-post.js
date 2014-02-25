/**
 * シングルポストで読み込む
 */
jQuery(document).ready(function($){
	var isMobile = $('body').hasClass('mobile');
	/**
	 * 段落の行頭揃え
	 */
	$('.post-content > p').each(function(index, elt){
		if($(elt).text().match(/^「/)){
			$(elt).addClass('no-indent');
		}
	});
	//フッターのリンク
	$('#post-feedback').css('top', $(window).height() - $('#post-feedback').height());
	//タッチデバイスの場合は2秒ごとに実行
	if(isMobile){
		timer = setInterval(function(){
			$('#post-feedback').css('top', window.innerHeight - $('#post-feedback').height());
		}, 2000);
	}
	$(window).resize(function(){
		$('#post-feedback').css('top', $(window).height() - $('#post-feedback').height());
	});
	//fancybox
	$('#post-feedback ul a, p.author a').each(function(index, elt){
		if($(elt).parent().hasClass('feedback')){
			$(elt).fancybox({
				type: 'iframe',
				openEffect: isMobile ? 'none' : 'fade',
				closeEffect: isMobile ? 'none' : 'fade' 
			});
		}else{
			$(elt).fancybox({
				minWidth: 560,
				maxWidth: 740,
				openEffect: isMobile ? 'none' : 'fade',
				closeEffect: isMobile ? 'none' : 'fade' 
			});
		}
	});
});