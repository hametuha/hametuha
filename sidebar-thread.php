<div id="sidebar">
	<?php /*
	<div class="widgets thread-widget" id="thread-widget">
		<h2 class="widget-title">スレッド内検索</h2>
		<form method="get" action="<?php echo get_post_type_archive_link('thread');?>">
			<input type="text" name="s" value="<?php the_search_query(); ?>" />
			<input type="submit" value="検索" />
		</form>
	</div>
	 * 
	 */ ?>
	<?php dynamic_sidebar('thread-sidebar'); ?>
</div>