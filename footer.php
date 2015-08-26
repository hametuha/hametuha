<footer id="footer">
	<div id="footer-sidebar" class="container">
		<div class="col-sm-4">
			<h3 class="text-center">破滅派とは？</h3>
			<?php wp_nav_menu( array(
				'theme_location' => 'hametuha_global_about',
				'container'      => false,
				'menu_class'     => 'nav nav-pills nav-stacked'
			) ); ?>
		</div>
		<div class="col-sm-4">
			<h3 class="text-center">関連リンク</h3>
			<ul class="nav nav-pills nav-stacked external-links">
				<li><a href="http://hametuha.co.jp"><i class="icon-ha"></i> <span>株式会社破滅派</span></a></li>
				<li><a href="http://hametuha.tokyo"><i class="icon-cup"></i> <span>破滅サロン</span></a></li>
				<li><a href="https://www.facebook.com/hametuha.inc"><i class="icon-facebook4"></i> <span>Facebook</span></a>
				</li>
				<li><a href="https://twitter.com/hametuha"><i class="icon-twitter"></i> <span>Twitter</span></a></li>
				<li><a href="https://plus.google.com/b/115001047459194790006/115001047459194790006/about/p/pub"
					   rel="publisher"><i class="icon-googleplus3"></i> <span>Google+</span></a></li>
				<li><a href="http://www.ustream.tv/channel/<?= rawurlencode( '破滅派' ) ?>"><i class="icon-ustream"></i>
						<span>Ustream</span></a></li>
				<li><a href="https://www.youtube.com/user/hametuha"><i class="icon-youtube"></i>
						<span>Youtube</span></a></li>
				<li><a href="http://minico.me"><i class="icon-minicome"></i> <span>ミニコme!</span></a></li>
				<li><a href="https://github.com/hametuha/"><i class="icon-github3"></i> <span>Github</span></a></li>
			</ul>
		</div>
		<div class="col-sm-4">
			<h3 class="text-center">破滅派の本</h3>
			<?php

			$kdp_query = new WP_Query( [
				'post_type'      => 'series',
				'post_status'    => 'publish',
				'meta_filter'    => 'kdp',
				'posts_per_page' => 6,
			] );
			if ( $kdp_query->have_posts() ):
				?>
				<div id="carousel-generic" class="carousel slide" data-ride="carousel" data-slide="next">
					<!-- Indicators -->
					<ol class="carousel-indicators">
						<?php for ( $i = 0, $l = $kdp_query->post_count; $i < $l; $i ++ ) : ?>
							<li data-target="#carousel-generic" data-slide-to="<?= $i ?>"
								class="<?= $i ? '' : 'active' ?>"></li>
						<?php endfor; ?>
					</ol>

					<!-- Wrapper for slides -->
					<div class="carousel-inner" role="listbox">
						<?php $activated = false;
						while ( $kdp_query->have_posts() ): $kdp_query->the_post(); ?>

							<a href="<?php the_permalink() ?>" class="item<?= $activated ? '' : ' active' ?>"
							   style="background-image: url('<?php if ( has_post_thumbnail() )
								   echo wp_get_attachment_image_src( get_post_thumbnail_id(), 'medium' )[0] ?>');">
								<div class="carousel-caption">
									<h2 class="carousel-caption__title">
										<?php the_title() ?>
									</h2>

									<p class="carousel-caption__lead"><?php the_author() ?></p>
								</div>
							</a>
							<?php $activated = true; endwhile;
						wp_reset_postdata(); ?>

					</div>
					<!-- //.carousel-inner -->

					<!-- Controls -->
					<a class="left carousel-control" href="#carousel-generic" role="button" data-slide="prev">
						<span class="icon-arrow-left" aria-hidden="true"></span>
						<span class="sr-only">Previous</span>
					</a>
					<a class="right carousel-control" href="#carousel-generic" role="button" data-slide="next">
						<span class="icon-arrow-right2" aria-hidden="true"></span>
						<span class="sr-only">Next</span>
					</a>
				</div><!-- //.carousel -->
			<?php endif; ?>        </div>
	</div>

	<p class="copy-right text-center">
		&copy; <span itemprop="copyrightYear">2007</span> 破滅派
	</p>
</footer>
<?php get_template_part( 'parts/modal' ) ?>
<?php wp_footer(); ?>
</body>
</html>
