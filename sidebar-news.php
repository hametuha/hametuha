<div class="col-xs-12 col-md-3" id="sidebar" role="navigation">

	<?php dynamic_sidebar( 'news-sidebar' ); ?>

	<div class="widget">
		<h2 class="widget-title"><i class="icon-star"></i> 新着ニュース</h2>
		<ul class="news-list news-list__vertical">
			<?php
			$recent = new WP_Query( [
				'post_type' => 'news',
			    'post_status' => 'publish',
			    'posts_per_page' => 5,
			] );
			while ( $recent->have_posts() ) {
				$recent->the_post();
				get_template_part( 'parts/loop', 'news' );
			}
			wp_reset_postdata();
			?>
		</ul>
		<p class="m20">
			<a href="<?= get_post_type_archive_link( 'news' ) ?>" class="btn btn-default btn-block">もっと見る</a>
		</p>
	</div>

	<div class="widget">

		<h2 class="widget-title"><i class="icon-tag"></i> カテゴリー</h2>
		<ul>
		<?php wp_list_categories( [
			'taxonomy' => 'genre',
		    'title_li' => '',
		] ) ?>
		</ul>
	</div>

	<div class="widget">
		<?php google_adsense( 3 ) ?>
		<p class="news-ad__title">Ads by Google</p>
	</div>

</div><!-- //#sidebar -->
