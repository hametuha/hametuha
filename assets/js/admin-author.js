/**
 * 管理画面で投稿者用に利用される関数
 */
jQuery(document).ready(function($){
	$('#taxonomy-category input[type=checkbox]').each(function(index, elt){
		elt.type = 'radio';
	});
	$('.category-checklist input[type=checkbox]').each(function(index, elt){
		elt.type = 'radio';
	});
	//抜粋
	$('#postexcerpt h3 span').text('抜粋（あらすじ）');
	//投稿フォーマットの表記を変更
	$('#formatdiv label').each(function(index, elt){
		switch($(elt).text()){
			case '標準':
				$(elt).text('縦書き（文字もの中心）');
				break;
			case '画像':
				$(elt).text('横書き（ブログ形式）');
				break;		}
	});
	$('span.post-state-format').each(function(index, elt){
		switch($(elt).text()){
			case '画像':
				$(elt).text('横書き（ブログ形式）');
				break;
		}
	});
	//カテゴリー
	if($('#categorydiv h3 span').text() == 'カテゴリー'){
		$('#categorydiv h3 span').text('カテゴリー（ジャンル）');
	}
	
});
