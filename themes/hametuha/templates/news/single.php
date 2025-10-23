<?php
/**
 * ニュース個別ページテンプレート
 *
 * @feature-group news
 */
get_header();
?>

	<div class="container mt-3">
		<?php get_header( 'breadcrumb' ); ?>
	</div>

	<div class="container single mt-3">

		<div class="row">

			<?php
			if ( have_posts() ) :
				while ( have_posts() ) :
					the_post();
					?>

					<article <?php post_class( 'col-xs-12 col-md-9 main-container' ); ?>>

						<?php get_template_part( 'parts/meta', 'thumbnail' ); ?>

						<!-- title -->
						<div class="page-header mt-3">

							<h1 class="post-title news__title">
								<?php the_title(); ?>
								<?php if ( hamenew_is_pr() ) : ?>
									<small>（PR記事）</small>
								<?php endif; ?>
							</h1>

						</div><!-- //.page-header -->

						<!-- Meta data -->
						<div <?php post_class( 'post-meta' ); ?>>

							<?php get_template_part( 'parts/meta', 'single' ); ?>

						</div><!-- //.post-meta -->

						<?php if ( has_excerpt() ) : ?>
							<div class="news-excerpt">
								<?php the_excerpt(); ?>
							</div>
						<?php endif; ?>


						<div class="row news-ad news-ad--head">
							<p class="news-ad__title">Ads by Google</p>
							<?php google_adsense( 1 ); ?>
						</div>

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

						<?php get_template_part( 'parts/author', 'narrow' ); ?>


						<?php get_template_part( 'parts/event', 'address' ); ?>


						<?php if ( $links = hamenew_links() ) : ?>
							<div class="row news-related">
								<div class="col-xs-12">
									<h2 class="news-related__title"><i class="icon-link"></i> この記事の関連リンク</h2>
									<ul class="news-related__list">
										<?php foreach ( $links as list( $title, $url ) ) : ?>
											<li class="news-related__item">
												<i class="icon-arrow-right3"></i>
												<a href="<?php echo esc_url( $url ); ?>" target="_blank"
													class="news-related__link">
													<?php echo esc_html( $title ); ?> <i class="icon-popout"></i>
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
										<?php foreach ( $links as list( $title, $url, $src, $author, $publisher, $rank ) ) : ?>
											<li class="news-books__item">
												<a href="<?php echo esc_url( $url ); ?>" target="_blank"
													class="news-books__link clearfix">
													<?php if ( $src ) : ?>
														<img src="<?php echo esc_url( $src ); ?>"
															alt="<?php echo esc_attr( $title ); ?>"
															class="news-books__image">
													<?php endif; ?>
													<p class="news-books__desc">
														<span
															class="news-books__name"><?php echo esc_html( $title ); ?></span>
														<?php if ( $author ) : ?>
															<span class="news-books__author">
															<i class="icon-user"></i> <?php echo esc_html( $author ); ?>
														</span>
														<?php endif; ?>
														<?php if ( $publisher ) : ?>
															<span class="news-books__publisher">
															<i class="icon-office"></i> <?php echo esc_html( $publisher ); ?>
														</span>
														<?php endif; ?>
														<span class="news-books__rank">
														<i class="icon-crown"></i> <?php echo is_numeric( $rank ) ? number_format_i18n( (int) $rank ) : '-'; ?>
														位
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

						<div class="shareContainer__wrapper mb-5 mt-5">
							<h2 class="series__title--share text-center">
								<small class="series__title--caption">Share This</small>
								ニュースを広める
							</h2>
							<p class="text-muted text-center">
								このニュースが気になったらSNSでシェアしてください。
							</p>
							<?php get_template_part( 'parts/share' ); ?>
						</div>

						<div class="row mb20">
							<?php google_adsense( 'related' ); ?>
						</div>

						<div class="row">

							<div class="col-xs-12 col-sm-6 news-ad--content">
								<?php google_adsense( 2 ); ?>
								<p class="news-ad__title">Ads by Google</p>
							</div>

							<div class="col-xs-12 col-sm-6 news-related">
								<h3 class="list-title news-related__title">キーワード</h3>
								<p class="news-keywords__wrapper">
									<?php if ( $terms = hametuha_get_nouns() ) : ?>
										<?php
										echo implode( ' ', array_map( function ( $term ) {
											return sprintf(
												'<a href="%s" class="news-keywords__link"><i class="icon-tag6"></i> %s(%s)</a>',
												get_term_link( $term ),
												esc_html( $term->name ),
												$term->count > 100 ? '99+' : number_format( $term->count )
											);
										}, $terms ) );
										?>
									<?php else : ?>
										関連するキーワードのニュースはありません。
									<?php endif; ?>
								</p>
							</div>

						</div>

						<?php if ( ! is_preview() ) : ?>
							<hr />
							<div class="row news-comment">
								<h2>コメント
									<small>Facebookコメントが利用できます</small>
								</h2>
								<div class="fb-comments" data-href="<?php the_permalink(); ?>" data-width="100%"
									data-numposts="5"></div>
							</div>
						<?php endif; ?>

						<hr />


						<ul class="news-pager">
							<?php previous_post_link( '<li class="previous">%link</li>', '<i class="icon-arrow-left"></i><small>PREVIOUS POST</small><br />%title' ); ?>
							<?php next_post_link( '<li class="next">%link</li>', '<i class="icon-arrow-right2"></i><small>NEXT POST</small><br />%title' ); ?>
						</ul>
						<?php get_template_part( 'templates/news/jumbotron' ); ?>


					</article><!-- //.single-container -->

				<?php
				endwhile;
			endif;
			?>

			<?php get_sidebar( 'news' ); ?>

		</div><!-- //.row -->

		<h2 class="page-header text-center">
			<small>eBooks</small><br />
			破滅派の電子書籍
		</h2>
		<?php
		hameplate( 'templates/recommendations', '', [
			'author' => get_the_author_meta( 'ID' ),
			'fill'   => true,
		] )
		?>

	</div><!-- //.container -->
<?php
get_footer( 'books' );
get_footer();
