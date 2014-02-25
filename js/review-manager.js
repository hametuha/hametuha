/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

jQuery(document).ready(function($){
	//スターレーティング
	if($('.ranker').length == 1){
		$('.ranker a').each(function(index, a){
			$(a).click(function(e){
				e.preventDefault();
				$(this).prevAll('input').val(index + 1);
				$(this).prevAll('a').addClass('active');
				$(this).addClass('active');
				$(this).nextAll('a').removeClass('active');
			});
			$(a).hover(function(e){
				$(this).prevAll('a').addClass('hover');
			}, function(e){
				$(this).prevAll('a').removeClass('hover');
			});
		});
	}
	//レビュー修正
	$('.edit-feedback').fancybox({
		width: 700,
		type: 'iframe',
		afterClose: function(e){
			window.location.reload();
		}
	});
});
