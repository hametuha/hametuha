<div class="news-eyecatch container">
	<div class="news-eyecatch-text">
		<h1 class="news-eyecatch-title">文芸にもニュースを。</h1>
		<div class="news-eyecatch-lead">
			<?php echo apply_filters( 'the_content', get_post_type_object( 'news' )->description ); ?>
		</div>
	</div>
</div><!-- //.news-eyecatch -->
