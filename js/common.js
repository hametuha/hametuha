/* 
 * 破滅派サイト全体で共通して読み込まれるファイル
 */

jQuery(document).ready(function($){
	//なかのひと
	if(document.location.href.match(/hametuha\.com/)){
		$('#footer-sidebar>div:eq(3)').append(
			"<p class=\"center\"><a href='http://nakanohito.jp/'>"
			+ "<img src='http://nakanohito.jp/an/?u=230742&h=1038549&w=128&guid=ON&t=&version=js&refer="+escape(parent.document.referrer)+"&url="+escape(parent.document.URL)+"' border='0' width='128' height='128' />"
			+ "</a></p>"
		);
	}
	//qTip
	$('img.tooltip').qtip({
		content:{
			attr: 'alt'
		},
		style: {
			classes: 'ui-tooltip ui-tooltip-shadow ui-tooltip-rounded'
		}
	});
	//フォームの二重送信防止
	$('form').submit(function(e){
		$(this).find('input[type=submit]').attr('disabled', true);
	});
	//リストのタップ
	if($('.works-list td.more').length > 0){
		$('.works-list tr').click(function(e){
			window.location.href = $(this).find('td.more a').attr('href');
		});
	}
});

