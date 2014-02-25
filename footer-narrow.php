<div id="footer" class="wrap">
	<div id="footer-sidebar" class="clearfix">
		<?php dynamic_sidebar("footer-sidebar-new"); ?>
	</div>
	
	<div class="footer-note clearfix">
		<p class="copy-right alignleft serif">
			<?php if(!(is_singular('post') || is_singular('series'))) echo '&copy;'; ?> 2007-<?php echo date_i18n('Y'); ?> HAMETUHA
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