	</div><!-- // .content -->
<?php
	//Load Sideba by it's context
	if(needs_left_sidebar()){
		get_sidebar('left');
	}
	if(is_tax('faq_cat') || is_post_type_archive('faq') || is_singular('faq')){
		get_sidebar('faq');
	}elseif(is_singular ('anpi') || is_post_type_archive ('anpi') || is_tax('anpi_cat')){
		get_sidebar('anpi');
	}elseif(is_singular('announcement') || is_post_type_archive('announcement')){
		get_sidebar('announcement');
	}elseif(is_singular('post') || is_author() || is_singular('series') || (defined('IS_PROFILE_PAGE') && IS_PROFILE_PAGE) ){
		get_sidebar('post');
	}elseif(is_singular('thread') || is_post_type_archive('thread') || is_tax('topic')){
		get_sidebar('thread');
	}else{
		get_sidebar();
	}
?>

</div>
<!-- #main ends -->

<div id="footer">
	<div id="footer-sidebar" class="margin clearfix">
		<?php dynamic_sidebar("footer-sidebar"); ?>
	</div>
	<div class="margin footer-note clearfix">
		<p class="copy-right alignleft serif">
			&copy; 2007-<?php echo date_i18n('Y'); ?> HAMETUHA
		</p>
		<p class="related alignright">
			<a href="http://hametuha.co.jp">Web制作・電子書籍</a>｜
			<a href="http://minico.me">ミニコミ販売ポータル</a>
		</p>
	</div>
</div>

<?php wp_footer(); ?>
</body>
</html>