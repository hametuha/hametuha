<div id="subnavigation">
	<div class="widget">
		<h2 class="widget-title search">検索</h2>
		<div class="widget-content search-widget">
			<?php get_search_form(); ?>
		</div>
	</div>
	<div class="widget">
		<h2 class="widget-title category">カテゴリー</h2>
		<div class="widget-content category-widget">
			<ul>
			<?php wp_list_categories('depth=1&title_li=&show_count=1'); ?>
			</ul>
		</div>
	</div>
	<?php dynamic_sidebar("left-sidebar"); ?>
</div>