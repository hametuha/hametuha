<?php get_header(); ?>

<?php get_header( 'breadcrumb' ) ?>

	<div class="container single">

		<div class="row">

			<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

				<article <?php post_class( 'col-xs-12 col-md-9 main-container' ) ?>>

					<!-- title -->
					<div class="page-header">

						<h1 class="post-title news__title">
							<?php the_title(); ?>
							<?php if ( hamenew_is_pr() ) : ?>
								<small>（PR記事）</small>
							<?php endif; ?>
						</h1>

					</div><!-- //.page-header -->

					<!-- Meta data -->
					<div <?php post_class( 'post-meta' ) ?>>

						<?php get_template_part( 'parts/meta', 'single' ) ?>

					</div><!-- //.post-meta -->


					<div class="row news-ad news-ad--head">
						<p class="news-ad__title">Ads by Google</p>
						<?php google_adsense( 1 ) ?>
					</div>
					
					<?php get_template_part( 'parts/meta', 'thumbnail' ) ?>

					<?php if ( has_excerpt() ) : ?>
					<div class="news-excerpt">
						<?php the_excerpt() ?>
					</div>
					<?php endif; ?>


					<div class="post-content clearfix">
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

					<div class="news-author row">
						<a class="news-author__link clearfix"
						   href="<?= home_url( sprintf( '/doujin/detail/%s/', get_the_author_meta( 'user_nicename' ) ) ) ?>">
							<?= get_avatar( get_the_author_meta( 'ID' ), 48, '', get_the_author(), [ 'class' => ' img-circle news-author__img' ] ) ?>
							文責:
							<span class="news-author__name"><?php the_author() ?></span>
							<small
								class="news-author__position"><?= hametuha_user_role( get_the_author_meta( 'ID' ) ) ?></small>
								<span class="news-author__desc">
									<?= trim_long_sentence( get_the_author_meta( 'description' ) ) ?>
								</span>
						</a>
					</div><!-- .news-author -->



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
								<?php elseif ( $post->_event_end ) : ?>
									<p class="news-event__date">
										<strong><i class="icon-calendar"></i> 〆切</strong> <?= mysql2date( 'Y年n月j日（D）', $post->_event_end ) ?>
										<?php if ( strtotime( $post->_event_end ) < current_time( 'timestamp', true ) ) : ?>
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
										<iframe width="100%" height="100%" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://www.google.com/maps/embed/v1/place?key=AIzaSyDDZqyowmW69rWqntGmiYRg1u3tira2Wm8&q=<?= rawurlencode( $post->_event_address ) ?>"></iframe>
									</div>
								</div><!-- -->

							<?php endif; ?>


						</div>
					<?php endif; ?>


					<?php if ( $links = hamenew_links() ) : ?>
						<div class="row news-related">
							<div class="col-xs-12">
								<h2 class="news-related__title"><i class="icon-link"></i> この記事の関連リンク</h2>
								<ul class="news-related__list">
									<?php foreach ( $links as list( $title, $url ) ) : ?>
									<li class="news-related__item">
										<i class="icon-arrow-right3"></i>
										<a href="<?= esc_url( $url ) ?>" target="_blank" class="news-related__link">
											<?= esc_html( $title ) ?> <i class="icon-popout"></i>
										</a>
									</li>
									<?php endforeach; ?>
								</ul>
							</div>
						</div><!-- //.news-related -->
					<?php endif; ?>



					<?php if ( $links = hamenew_books() ) : ?>
						<div class="row news-books">
							<div class="col-xs-12">
								<h2 class="news-books__title"><i class="icon-books"></i> この記事の関連書籍など</h2>
								<ul class="news-books__list">
									<?php foreach ( $links as list(  $title, $url, $src, $author, $publisher, $rank ) ) : ?>
										<li class="news-books__item">
											<a href="<?= esc_url( $url ) ?>" target="_blank" class="news-books__link clearfix">
												<?php if ( $src ) : ?>
													<img src="<?= $src ?>" alt="<?= esc_attr( $title ) ?>" class="news-books__image">
												<?php endif; ?>
												<p class="news-books__desc">
													<span class="news-books__name"><?= esc_html( $title ) ?></span>
													<?php if ( $author ) : ?>
														<span class="news-books__author">
															<i class="icon-user"></i> <?= esc_html( $author ) ?>
														</span>
													<?php endif; ?>
													<?php if ( $publisher ) : ?>
														<span class="news-books__publisher">
															<i class="icon-office"></i> <?= esc_html( $publisher ) ?>
														</span>
													<?php endif; ?>
													<span class="news-books__rank">
														<i class="icon-crown"></i> <?= $rank ? number_format_i18n( $rank ) : '-' ?>位
													</span><br />
													<span class="label label-warning">
														<i class="icon-amazon"></i> Amazonで見る
													</span>
												</p>
											</a>
										</li>
									<?php endforeach; ?>
								</ul>
								<p class="text-center text-muted news-books__note">
									Supported by amazon Product Advertising API
								</p>
							</div>
						</div><!-- //.news-related -->
					<?php endif; ?>

					<table class="news-follow">
						<tr>
							<?php
							$style = '';
							if ( has_post_thumbnail() ) {
								$style = sprintf( 'background-image: url(\'%s\');', get_the_post_thumbnail_url( null, 'large' ) );
							}
							?>
							<td class="news-follow__img" style="<?= $style ?>">
								&nbsp;
							</td>
							<td class="news-follow__link text-center">
								<p class="news-follow__lead">
									この記事よかった？<br />
									いいねしてね！
								</p>
								<div class="news-follow__like">
									<div class="fb-like" data-href="https://www.facebook.com/minicome/" data-layout="button" data-action="like" data-show-faces="false" data-share="false"></div>
								</div>
								<small class="news-follow__caption">ミニ子が更新情報お届け</small>
							</td>
						</tr>
					</table>

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
								if ( 2 > $term->count ) {
									return '';
								}
								return sprintf(
									'<a href="%s" class="news-keywords__link"><i class="icon-tag6"></i> %s(%s)</a>',
									get_term_link( $term ),
									esc_html( $term->name ),
									$term->count > 100 ? '99+' : number_format( $term->count )
								);
							}, $terms ) ); ?>
						</p>
					<?php endif; ?>

					<hr />
					<div class="row news-comment">
						<h2>コメント<small>Facebookコメントが利用できます</small></h2>
						<div class="fb-comments" data-href="<?php the_permalink() ?>" data-width="100%" data-numposts="5"></div>
					</div>

					<hr/>


					<ul class="news-pager">
						<?php previous_post_link(  '<li class="previous">%link</li>', '<i class="icon-arrow-left"></i><small>PREVIOUS POST</small><br />%title'  ); ?>
						<?php next_post_link(  '<li class="next">%link</li>', '<i class="icon-arrow-right2"></i><small>NEXT POST</small><br />%title'  ); ?>
					</ul>

					<?php get_template_part( 'parts/jumbotron', 'news' ) ?>

				</article><!-- //.single-container -->

			<?php endwhile; endif; ?>

			<?php get_sidebar( 'news' ) ?>

		</div><!-- //.row -->


	</div><!-- //.container -->

<?php get_footer();
