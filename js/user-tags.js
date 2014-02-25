jQuery(document).ready(function($){
	var tagManager = {
		
		/**
		 * メッセージを出力する
		 * @param {String} string
		 * @param {String} className success, error, warningのいずれか
		 */
		message: function(string, className){
			$(document.createElement('p')).addClass('small-message').addClass(className).text(string).css({
				display: 'none'
			}).prependTo($('#user-tag-editor .message-box')).fadeIn('fast', function(){
				$(this).delay(5000).fadeOut('fast', function(){
					$(this).remove();
				});
			});
		},
		
		/**
		 * フォームが送信されたときに実行される
		 * @param {Event} e
		 */
		submit: function(e){
			e.preventDefault();
			var tagText = $(this).find('input[name=user_tag]').val();
			if(tagText.length == 0){
				tagManager.message('タグが空ですよ！', 'warning');
			}else if(tagText.length >= 18){
				tagManager.message('タグは17文字まで！', 'warning');
			}else{
				tagManager.addTag(tagText);
			}
		},
		
		/**
		 * タグを追加する
		 * @param {String} tagText
		 */
		addTag: function(tagText){
			$('#user-tag-editor').find('.loader').addClass('loading');
			$.post(
				HametuhaUserTags.endpoint,
				{
					action: HametuhaUserTags.addTag,
					_wpnonce: HametuhaUserTags.nonce,
					term: tagText,
					post_id: HametuhaUserTags.postID
				},
				function(result){
					$('#user-tag-editor .loader').removeClass('loading');
					if(result.code > 0){
						$('#user-tag-container span').remove();
						tagManager.deletable($('#user-tag-container').prepend(result.html).fadeIn('fast'));
						tagManager.message(result.message, 'success');
						$('#user-tag-editor input[name=user_tag]').val('');
					}else{
						tagManager.message(result.message, 'error');
					}
				}
			);
		},
		
		/**
		 * @param {Object} targetLink
		 */
		deletable: function(targetLink){
			var link = $(targetLink);
			link.find('small').click(function(e){
				e.preventDefault();
				if(confirm('このタグを削除してもよろしいですか？')){
					var tagID = parseInt($(this).parent('a').attr('id').replace(/[^0-9]/g, ''), 10);
					if(tagID > 0){
						$.post(
							HametuhaUserTags.endpoint,
							{
								action: HametuhaUserTags.deleteTag,
								_wpnonce: HametuhaUserTags.nonce,
								post_id: HametuhaUserTags.postID,
								term_id: tagID
							},
							function(result){
								if(result.status){
									tagManager.message(result.message, 'success');
									link.fadeOut('normal', function(){
										$(this).remove();
									});
								}else{
									tagManager.message(result.message, 'error');
								}
							}
						);
					}
				}
			});
		},
		
		/**
		 * @param {Object} tragetLink
		 */
		addable: function(targetLink){
			$(targetLink).find('small').click(function(e){
				e.preventDefault();
				var tagText = $(this).parent('a').text().replace(/＋$/, '');
				if(confirm('「' + tagText + '」というタグを追加しますか？')){
					tagManager.addTag(tagText);
				}
			});
		},
		
		/**
		 * キーワードをカウントする
		 * @param {Event} e
		 */
		tagCount: function(e){
			var length = $(this).val().length;
			var target = $('#user-tag-editor p.tag-counter span');
			if(length > 17){
				target.addClass('tag-counter-error').text( (17 - length) + '文字オーバー');
			}else{
				target.removeClass('tag-counter-error');
				if(length >= 15){
					target.addClass('tag-counter-warning');
				}else{
					target.removeClass('tag-counter-warning');
				}
				target.text('あと' + (17 - length) + '文字');
			}
		}
	};
	
	//イベント添付
	$('#user-tag-editor').submit(tagManager.submit);
	$('#user-tag-editor input[name=user_tag]').keyup(tagManager.tagCount);
	$('#user-tag-container a').each(function(index, elt){
		tagManager.deletable(elt);
	});
	$('#all-user-tag-container a').each(function(index, elt){
		tagManager.addable(elt);
	});
});