<form method="get" action="<?php bloginfo('url'); ?>" class="adv-search-form">
	<?php if(is_post_type_archive('faq') || is_tag('cat_faq') || is_singular('faq')): ?>
	<input type="hidden" name="post_type" value="faq" />
	<?php else: ?>
	<p>
		<label>
			検索対象: 
			<select name="post_type">
				<option value="any">すべて</option>
				<option value="post">作品</option>
				<option value="faq">よくある質問</option>
				<option value="info">お知らせ</option>
				<option value="announcement">公式告知</option>
				<option value="anpi">安否情報</option>
			</select>
		</label>
	</p>
	<?php endif; ?>
	<p>
		<input type="text" class="short" name="s" value="<?php the_search_query(); ?>" />
		<input type="submit" class="submit button" value="検索" />
	</p>
</form>