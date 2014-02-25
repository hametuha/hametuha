<p id="user-tag-container" class="tag-container">
	<?php if(!the_current_user_tags('delete')): ?>
	<span>まだタグを登録していません。</span>
	<?php endif; ?>
</p>
<form id="user-tag-editor">
	<p class="tag-input clearfix">
		<input class="tag-value" type="text" name="user_tag" value="" placeholder="17文字以内で入力" />
		<input class="tag-submit" type="submit" value="＋" />
	</p>
	<p class="tag-counter right clearfix">
		<img class="loader alignleft" src="<?php bloginfo('template_directory'); ?>/img/ajax-loader.gif" width="16" height="11" />
		<span>あと17文字</span>
	</p>
	<div class="message-box"></div>
</form>