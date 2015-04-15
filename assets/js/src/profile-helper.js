/* 
 * プロフィール編集画面で読み込まれる関数
 */

/* global HametuhaProfile:true */

jQuery(document).ready(function($){

    "use strict";
	
	//ユーザー名が使用可能かチェックする
	$('#check-username-valid').click(function(e){
		e.preventDefault();
		var loader = $(this).next('img');
		var input = $(this).prev('input');
		loader.addClass('loading');
		$.post(HametuhaProfile.endpoint,{
			_wpnonce: HametuhaProfile.nonce,
			action: HametuhaProfile.usernameCheck,
			user_login: $(this).prev('input').val()
		}, function(result){
			loader.removeClass('loading');
			if(result.valid){
				input.removeClass('error');
			}else{
				input.addClass('error');
			}
			$('#your-profile').prev('p.message').remove();
			$('#your-profile').before('<p class="message ' + (result.valid ? 'success' : 'error') + '" style="display:none;">' + result.message + '</p>');
			$('#your-profile').prev('p.message').fadeIn();
		});
	});
	
	//フォームのバリデーション
	$('#your-profile').submit(function(e){
		//ユーザー名
		if(!$('input[name=user_login]').val().match(/^[0-9a-zA-Z.\-@_]+$/)){
			$('input[name=user_login]').addClass('error');
			if($(this).prev('p.message').length > 0){
				$(this).prev('p.message').remove();
			}
			$(this).before('<p class="message error" style="display:none;">ユーザー名に使用できるのは半角英数と_.-@のみです。</p>');
			$(this).prev('p.message').fadeIn();
			e.preventDefault();
		}
	});
	
	//リダイレクト
	if($('#login-success-redirect').length > 0){
		var startTime = 5;
		var timer = setInterval(function(){
			if(startTime <= 0){
				clearInterval(timer);
				window.parent.location.href = $('#login-success-redirect').next('a').attr('href');
			}else{
				startTime--;
				$('#login-success-redirect').text(startTime);
			}
		}, 1000);
	}
	
	//お気に入りの削除
	$('.delete-fav').click(function(e){
		e.preventDefault();
		if( window.confirm('このお気に入りフレーズを削除してよろしいですか？') ){
			var targetID = parseInt($(this).attr('id').replace(/delete_fav_/, ''));
			$(this).prev('.loader').addClass('loading');
			$.post(HametuhaProfile.endpoint,{
				_wpnonce: HametuhaProfile.nonce,
				action: HametuhaProfile.deleteFavorite,
				fav_id: targetID
			}, function(result){
				if(result.status){
					$('#li-fav-' + targetID).fadeOut('slow', function(e){
						$(this).remove();
					});
				}else{
					$('#li-fav-' + targetID + ' .loader').removeClass('loading');
					window.alert(result.message);
				}
				
			});
		}
	});
	
	//筆名読みがな
	$('input#last_name[type=text]').blur(function(e){
		var message = null;
		if($(this).val().length < 1){
			message = '筆名の読みが入力されていません';
		}else if(!$(this).val().match(/^[あ-ん 　]+$/)){
			message = '筆名の読みに使用できるのはひらがなだけです';
		}
		if(message){
			$('<p></p>').text(message).addClass('small-message').addClass('error').css('display', 'none').insertAfter($(this)).fadeIn('normal', function(){
				var p = this;
				var timer = setTimeout(function(){
					$(p).fadeOut('normal', function(){
						$(this).remove();
					});
				}, 3000);
			});
		}
	});
});