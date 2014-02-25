<div id="sidebar">
	<div class="widget">
		<h2 class="widget-title search">よくある質問を検索</h2>
		<div class="widget-content search-widget">
			<?php get_search_form(); ?>
		</div>
	</div>
	<div class="widget">
		<h2 class="widget-title search">カテゴリー</h2>
		<div class="widget-content search-widget">
			<dl id="faq-cats">
				<?php
					$cats = get_terms('faq_cat', 'orderby=ID&order=ASC');
					if(!empty($cats)) foreach($cats as $cat):
				?>
				<dt><?php echo $cat->name; ?></dt>
				<dd>
					
					<?php echo wpautop($cat->description); ?>
					<p class="right">
						<a href="<?php echo get_term_link($cat);?>" class="small-button"><?php echo number_format_i18n($cat->count); ?>件の記事</a>
					</p>
				</dd>
				<?php endforeach; ?>
			</dl>
		</div>
	</div>
	<?php dynamic_sidebar("faq-sidebar"); ?>
</div>