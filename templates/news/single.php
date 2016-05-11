<?php get_header(); ?>

<?php get_header( 'breadcrumb' ) ?>

	<div class="container single">

		<div class="row">

			<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

				<article <?php post_class( 'col-xs-12 col-sm-9 main-container' ) ?>>

					<!-- title -->
					<div class="page-header">

						<h1 class="post-title">
							<?php the_title(); ?>
							<?php if ( hamenew_is_pr() ) : ?>
								<small>（PR記事）</small>
							<?php endif; ?>
						</h1>

					</div><!-- //.page-header -->

					<!-- Meta data -->
					<div <?php post_class( 'post-meta' ) ?>>

						<?php get_template_part( 'parts/meta', 'single' ) ?>

						<div class="news-author">
							<a class="news-author__link clearfix"
							   href="<?= home_url( sprintf( '/doujin/detail/%s/', get_the_author_meta( 'user_nicename' ) ) ) ?>">
								<?= get_avatar( get_the_author_meta( 'ID' ), 48, '', get_the_author(), [ 'class' => ' img-circle news-author__img' ] ) ?>
								<span class="news-author__name"><?php the_author() ?></span>
								<small
									class="news-author__position"><?= hametuha_user_role( get_the_author_meta( 'ID' ) ) ?></small>
								<span class="news-author__desc">
									<?= trim_long_sentence( get_the_author_meta( 'description' ) ) ?>
								</span>
							</a>
						</div><!-- .news-author -->

					</div><!-- //.post-meta -->

					<div class="row news-ad news-ad--head">
						<?php google_adsense( 1 ) ?>
						<p class="news-ad__title">Ads by Google</p>
					</div>

					<?php get_template_part( 'parts/meta', 'thumbnail' ) ?>

					<div class="post-content clearfix" itemprop="articleBody">
						<?php the_content(); ?>
					</div><!-- //.post-content -->

					<?php
					wp_link_pages( [
						'before'      => '<div class="row"><p class="link-pages clrB">ページ: ',
						'after'       => '</p></div>',
						'link_before' => '<span>',
						'link_after'  => '</span>',
					] );
					?>

					<?php if ( $post->_event_title ) : ?>
						<div class="row news-event">

							<div class="col-sm-12 col-md-6 news-event__info">

								<h2 class="news-event__title"><?= esc_html( $post->_event_title ) ?></h2>

								<?php if ( $post->_event_start ) : ?>
									<p class="news-event__date">
										<strong><i class="icon-calendar"></i> 日時</strong> <?= hamenew_event_date( $post->_event_start, $post->_event_end ) ?>
										<?php if ( strtotime( $post->_event_end ?: $post->_event_start ) < current_time( 'timestamp', true ) ) : ?>
											<span class="label label-default">終了しました</span>
										<?php endif; ?>
									</p>
								<?php endif; ?>

								<?php if ( $post->_event_address ) : ?>
									<p class="news-event__address">
										<strong><i class="icon-map"></i> 場所</strong> <?= esc_html( $post->_event_address . ' ' . $post->_event_bld ) ?>
										<a href="https://maps.google.com/?q=<?= rawurlencode( $post->_event_address ) ?>" target="_blank">地図</a>
									</p>
								<?php endif; ?>

								<?php if ( $post->_event_desc ) : ?>
									<p class="news-event__desc">
										<?= nl2br( esc_html( $post->_event_desc ) ) ?>
									</p>
								<?php endif; ?>

							</div>


							<?php if ( $post->_event_address ) : ?>

								<div class="col-sm-12 col-md-6 news-event__map">
									<div class="news-event__map--inner">
										<iframe width="100%" height="100%" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://www.google.com/maps/embed/v1/place?key=AIzaSyDqiZUrqda0DVlw6HelWEbPLpcgFU0wXqM&q=<?= rawurlencode( $post->_event_address ) ?>"></iframe>
									</div>
								</div><!-- -->

							<?php endif; ?>


						</div>
					<?php endif; ?>


					<?php get_template_part( 'parts/share' ) ?>

					<div class="row">

						<div class="col-xs-12 col-sm-6 news-ad--content">
							<?php google_adsense( 2 ) ?>
							<p class="news-ad__title">Ads by Google</p>
						</div>

						<div class="col-xs-12 col-sm-6 news-related">
							<h3 class="list-title news-related__title">関連記事</h3>
							<ul class="news-list">
								<?php
								foreach ( hamenew_related() as $post ) {
									setup_postdata( $post );
									get_template_part( 'parts/loop', 'news' );
								}
								wp_reset_postdata();
								?>
							</ul>
						</div>

					</div>


					<?php if ( ( $terms = get_the_terms( get_post(), 'nouns' ) ) && ! is_wp_error( $terms ) ) : ?>
						<hr/>
						<h2 class="news-keywords__title">キーワード
							<small>このニュースに出てくる固有名詞</small>
						</h2>
						<p class="news-keywords__wrapper">
							<?= implode( ' ', array_map( function ( $term ) {
								return sprintf( '<a href="%s" class="news-keywords__link"><i class="icon-tag6"></i> %s</a>', get_term_link( $term ), esc_html( $term->name ) );
							}, $terms ) ); ?>
						</p>
					<?php endif; ?>

					<hr/>

					<?php get_template_part( 'parts/pager' ) ?>

				</article><!-- //.single-container -->

			<?php endwhile; endif; ?>

			<?php get_sidebar( 'news' ) ?>

		</div><!-- //.row -->

		<div class="row news-comment">
			<div class="fb-comments" data-href="<?php the_permalink() ?>" data-width="100%" data-numposts="5"></div>
		</div>

		<?php get_template_part( 'parts/jumbotron', 'news' ) ?>

	</div><!-- //.container -->

<?php get_footer();
