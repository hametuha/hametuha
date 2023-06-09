<?php get_header(); ?>

<?php get_header( 'breadcrumb' ); ?>

<div class="series__wrap" itemprop="mainEntity"  itemscope itemtype="http://schema.org/Book">

	<?php
	the_post();
	$series        = \Hametuha\Model\Series::get_instance();
	$rating        = \Hametuha\Model\Rating::get_instance();
	$collaborators = \Hametuha\Model\Collaborators::get_instance();
	$query         = \Hametuha\Model\Series::get_series_posts( get_the_ID(), 'publish', true );
	$all_reviews   = $series->get_reviews( get_the_ID(), true, 1, 12 );
	$ratings       = [];
	// Calc rating
	if ( $query->have_posts() ) {
		foreach ( $query->posts as $p ) {
			$avg        = $rating->get_post_rating( $p );
			$rate_count = $rating->get_post_rating_count( $p );
			if ( $rate_count ) {
				for ( $i = 0; $i < $rate_count; $i ++ ) {
					$ratings[] = $avg;
				}
			}
		}
	}
	foreach ( $all_reviews['rows'] as $review ) {
		if ( $review->rank ) {
			$ratings[] = (int) $review->rank;
		}
	}
	?>

	<div class="series__row series__row--cover">
		<div class="container series__inner" id="series-<?php the_ID(); ?>">

			<meta itemprop="bookFormat" content="EBook">

			<div class="row series__meta">
				<div class="col-xs-12 col-sm-4 series__meta--thumbnail text-center">
					<?php if ( has_post_thumbnail() ) : ?>
						<?php
						the_post_thumbnail( 'medium', [
							'itemprop' => 'image',
						] );
						?>
					<?php else : ?>
						<img itemprop="image" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/covers/printing.png"
							 alt="Now Printing..." width="300" height="480"/>
					<?php endif; ?>
				</div>

				<div class="col-xs-12 col-sm-8 series__top">

					<!-- title -->
					<div class="series__header">
						<h1 class="series__title">
							<span itemprop="name" class="series__title--work">
								<?php the_title(); ?>
								<?php if ( ( $subtitle = $series->get_subtitle( $post->ID ) ) ) : ?>
									<small itemprop="headline" class="series__title--headline"><?php echo esc_html( $subtitle ); ?></small>
								<?php endif; ?>
							</span>
							<small class="series__title--represent">
								<?php echo esc_html( hametuha_author_name() ); ?>（<?php echo esc_html( $collaborators->owner_label( get_the_ID() ) ); ?>）
							</small>
						</h1>
					</div>
					<!-- //.series__header -->

					<?php if ( has_excerpt() ) : ?>

						<div class="series__excerpt" itemprop="description">
							<?php the_excerpt(); ?>
						</div><!-- //.excerpt -->

					<?php endif; ?>

					<div class="series__link text-center">
						<?php
						switch ( $series->get_status( get_the_ID() ) ) :
							case 2:
								?>
								<a href="<?php echo $series->get_kdp_url( get_the_ID() ); ?>" class="btn btn-trans btn-lg btn-amazon"
								   data-outbound="kdp"
								   data-action="<?php echo esc_attr( $series->get_asin( get_the_ID() ) ); ?>"
								   data-label="<?php the_ID(); ?>"
								   data-value="<?php echo get_series_price(); ?>">
									<i class="icon-amazon"></i> Amazonで見る
								</a>
								<?php
								break;
							case 1:
								?>
								<a href="#series-notification" class="btn btn-trans btn-lg">
									<i class="icon-info2"></i> 販売準備中
								</a>
								<?php
								break;
							default:
								?>
								<?php
								break;
endswitch;
						?>
						<span class="series__link--divider"></span>
						<a href="#series-children" class="btn btn-trans">
							<i class="icon-books"></i> 掲載作一覧
						</a>
						<a href="#series-testimonials" class="btn btn-trans">
							<i class="icon-star"></i> レビュー
						</a>
					</div>

				</div>
			</div>


		</div>
	</div>
	<!-- //series__row--cover -->


	<div class="series__row series__row--meta">
		<div class="container series__inner">
			<div class="col-sm-4 hidden-xs"></div>
			<div class="col-sm-8 col-xs-12">
				<?php if ( 2 == $series->get_status( get_the_ID() ) ) : ?>
					<p class="series__price">
						&yen; <strong><?php the_series_price(); ?></strong>
					</p>
				<?php endif; ?>
				<ol class="series__status">
					<li>
						<?php
						$range = $series->get_series_range( get_the_ID() );
						if ( $series->is_finished( get_the_ID() ) ) :
							?>
							<i class="icon-checkmark3 ok"></i> 完結済み
							（
							<?php echo mysql2date( get_option( 'date_format' ), $range->start_date ); ?>
							〜
							<?php echo mysql2date( get_option( 'date_format' ), $range->last_date ); ?>
							）
						<?php else : ?>
							<i class="icon-info2 ng"></i> 連載中
							<small>
								（最終更新： <?php echo mysql2date( get_option( 'date_format' ), $range->last_date ); ?>
								）
							</small>
						<?php endif; ?>
					</li>
					<li>
						<i class="icon-books ok"></i> <?php echo number_format( $query->post_count ); ?> 作品収録
					</li>
					<li>
						<i class="icon-reading ok"></i>
						<?php
							$length = get_post_length();
							printf( '%1$s文字（400字詰原稿用紙%2$s枚）', number_format_i18n( $length ), number_format_i18n( ceil( $length / 400 ) ) );
						?>
					</li>
					<?php
					$afterwords = trim( $post->post_content );
					if ( ! empty( $afterwords ) ) :
						?>
						<li>
							<i class="icon-file-plus ok"></i>
							あとがき付き（約<?php echo number_format( mb_strlen( $post->post_content, 'utf-8' ) ); ?>文字）
						</li>
					<?php endif; ?>
				</ol>
			</div>
		</div>
	</div>

	<div class="series__row series__row--author">

		<div class="container series__inner">

			<div class="row">
				<div class="col-sm-4 col-xs-12">
					<h2 class="series__title--author">
						<small class="series__title--caption">Authors &amp; Editors</small>
						執筆者一覧
					</h2>
				</div>
				<div class="col-sm-8 col-xs-12">
					<?php
					$authors_to_display = $collaborators->get_published_collaborators( get_the_ID() );
					foreach ( $authors_to_display as $author ) :
						?>

						<div class="series__author">

							<div class="series__author--photo">
								<?php echo get_avatar( $author->ID, '150', '', $author->display_name, [ 'class' => 'img-circle' ] ); ?>
							</div>
							<!-- //.series__author -->

							<div class="series__author--profile">

								<h3>
									<span><?php echo esc_html( $author->display_name ); ?></span>
									<small><?php echo esc_html( $author->label ); ?></small>
								</h3>

								<?php $desc = get_user_meta( $author->ID, 'description', true ); ?>
								<div class="series__author--desc<?php echo 100 < mb_strlen( $desc, 'utf-8' ) ? ' series__author--longdesc' : ''; ?>">
									<?php echo wpautop( $desc ); ?>
								</div>

								<a class="btn btn-sm btn-link btn--author" href="<?php echo hametuha_author_url( $author->ID ); ?>" itemprop="url">
									詳しく見る
								</a>

							</div>
							<!-- //.series__author -->

						</div><!-- //.row -->

					<?php endforeach; ?>
				</div>
			</div>


		</div>
		<!-- //.container -->

	</div>


	<!-- //.series__row--author -->
	<div class="series__row series__row--children" id="series-children">

		<div class="container series__inner">

			<div class="row">
				<div class="col-sm-4 col-xs-12">
					<h2 class="series__title--list">
						<small class="series__title--caption">Works</small>
						収録作一覧
					</h2>
				</div>

				<div class="col-sm-8 col-xs-12">
					<?php if ( $query->have_posts() ) : ?>
						<ol class="series__list">
							<?php
							$counter = 0;
							while ( $query->have_posts() ) {
								$counter++;
								$query->the_post();
								hameplate( 'parts/loop-series', get_post_type(), [
									'counter' => $counter,
								] );
							}
							wp_reset_postdata();
							?>
						</ol>

					<?php else : ?>

						<div class="alert alert-warning">
							<p>まだ作品が登録されていません。<a class="alert-link" href="#series-notification">破滅派をフォロー</a>して、作者の活躍に期待してください。
							</p>
						</div>

						<?php
					endif;
					wp_reset_postdata();
					?>
				</div>
			</div>


		</div>
		<!-- //.container -->

	</div>
	<!-- series_row--children -->


	<div class="series__row series__row--testimonials" id="series-testimonials">

		<div class="container series__inner">

			<div class="row">
				<div class="col-xs-12">
					<h2 class="series__title--testimonials text-center">
						<small class="series__title--caption">How people say</small>
						みんなの反応
					</h2>
				</div>
			</div>
			<!-- //.series__title--testimonials -->

			<div class="row series__testimonials--container">
				<hr/>
				<?php if ( $ratings ) : ?>
					<p class="series__testimonials--stars text-center" itemprop="aggregateRating" itemscope
					   itemtype="http://schema.org/AggregateRating">
						<?php
						$avg = number_format( array_sum( $ratings ) / count( $ratings ), 2 );
						$avg = 4.5;
						for ( $i = 1; $i <= $avg; $i ++ ) {
							echo '<i class="icon-star6"></i>';
							if ( $i + 1 > $avg && $i + 0.5 <= $avg ) {
								echo '<i class="icon-star5"></i>';
							}
						}
						?>
						<br/>
						<strong itemprop="ratingValue"><?php echo $avg; ?></strong><br/>
						<small>（<span itemprop="reviewCount"><?php echo count( $ratings ); ?></span>件の評価）</small>
						<meta itemprop="worstRating" content="1">
						<meta itemprop="bestRating" content="5">
					</p>
					<hr/>
				<?php endif; ?>

				<ol id="series-testimonials-list" class="testimonial-list">
					<?php foreach ( $all_reviews['rows'] as $review ) : ?>
						<li itemprop="review" itemscope itemtype="http://schema.org/Review"
							class="testimonial-item">
							<meta itemprop="datePublished" content="<?php echo $review->comment_date; ?>">
							<?php if ( $review->twitter ) : ?>
								<?php show_twitter_status( $review->comment_author_url ); ?>
								<meta itemprop="author" content="<?php echo preg_replace( '@https://twitter.com/([^/]+)/.+@u', '@$1', $review->comment_author_url ); ?>">
							<?php else : ?>
								<blockquote class="testimonial-text">
									<div itemprop="reviewBody">
										<?php echo wpautop( apply_filters( 'get_comment_text', ( get_comment_meta( $review->comment_ID, 'comment_excerpt', true ) ?: $review->comment_content ), $review ) ); ?>
									</div>
									<?php if ( $review->rank ) : ?>
										<p class="testimonial-rating" itemprop="reviewRating"
										   itemscope itemtype="http://schema.org/Rating">
											<?php for ( $j = 0; $j < $review->rank; $j ++ ) : ?>
												<i class="icon-star6"></i>
											<?php endfor; ?>
											<meta itemprop="ratingValue" content="<?php echo esc_attr( $review->rank ); ?>">
											<meta itemprop="worstRating" content="1">
											<meta itemprop="bestRating" content="5">
										</p>
									<?php endif; ?>
									<?php
									$icon = '';
									if ( $review->amazon ) {
										$url  = $series->get_kdp_url( get_the_ID() );
										$icon = '<i class="icon-amazon"></i> Amazonレビュー';
									} elseif ( $review->comment_post_ID != get_the_ID() ) {
										$url  = get_comment_link( $review );
										$icon = 'from 破滅派';
									} elseif ( preg_match( '#^https?://.+#u', $review->comment_author_url ) ) {
										$url  = $review->comment_author_url;
										$icon = 'from ' . esc_html( $review->domain );
									} else {
										$url = '';
									}
									?>
									<cite class="testimonial-source">
										<?php if ( $url ) : ?>
											<a href="<?php echo esc_url( $url ); ?>" rel="nofollow" target="_blank"
											   itemprop="author">
												<?php echo esc_html( $review->comment_author ); ?>
											</a>
											<small><?php echo $icon; ?></small>
										<?php else : ?>
											<span itemprop="author"><?php echo esc_html( $review->comment_author ); ?></span>
										<?php endif; ?>
									</cite>
								</blockquote>
							<?php endif; ?>
						</li>
						<?php endforeach; ?>
				</ol>
				<p class="text-center">

					<?php if ( is_user_logged_in() ) : ?>
						<a class="review-creator btn btn-primary btn-lg" rel="nofollow"
						   href="<?php echo home_url( '/testimonials/add/' . get_the_ID() . '/', is_ssl() ? 'https' : 'http' ); ?>"
						   data-title="<?php echo sprintf( '%sのレビュー', esc_attr( get_the_title() ) ); ?>">
							<i class="icon-bubble6"></i> レビュー追加
						</a>
						<?php if ( current_user_can( 'edit_post', get_the_ID() ) ) : ?>
							<a class="btn btn-default btn-lg" rel="nofollow"
							   href="<?php echo home_url( '/testimonials/manage/' . get_the_ID() . '/', 'https' ); ?>">
								<i class="icon-bubble6"></i> 管理
							</a>
						<?php endif; ?>
					<?php else : ?>
						<a class="btn btn-primary btn-lg" href="<?php echo wp_login_url( get_permalink() ); ?>">
							<i class="icon-enter3"></i> ログインしてレビュー
						</a>
					<?php endif; ?>
				</p>
			</div>

		</div>
		<!-- //.container -->
	</div>
	<!-- //.series__row--testimonials -->



	<?php if ( $url = $series->get_kdp_url( get_the_ID() ) ) : ?>
		<div class="series__row series__row--amazon" itemprop="offers" itemscope itemtype="http://schema.org/Offer">
			<div class="row">
				<div class="col-xs-12">

					<h2 class="series__title--amazon text-center">
						<small class="series__title--caption">Buy at Amazon</small>
						購入する
					</h2>
					<p class="series__price text-center">
						&yen; <strong itemprop="price"><?php the_series_price(); ?></strong>
						<meta itemprop="priceCurrency" content="JPY" />
					</p>

					<p class="text-muted text-center">
						AmazonのKindleストアで購入できます。
					</p>

					<p class="text-center">
						<a href="<?php echo $url; ?>" class="btn btn-trans btn-lg btn-amazon"
						   itemprop="availability"
						   data-outbound="kdp"
						   data-action="<?php echo esc_attr( $series->get_asin( get_the_ID() ) ); ?>"
						   data-label="<?php the_ID(); ?>"
						   data-value="<?php echo get_series_price(); ?>">
							<i class="icon-amazon"></i> この本を購入する
						</a>
					</p>

					<p class="text-muted text-center">
						<strong>※ Kindle以外にもスマートフォンやPCの無料アプリで読めます。</strong><br/>
						<a href="http://www.amazon.co.jp/gp/aw/rd.html?ie=UTF8&a=B00QJDOM6U&at=hametuha-22&dl=1&lc=msn&uid=NULLGWDOCOMO&url=%2Fgp%2Faw%2Fd.html" alt="Kindle Paperwhite (ニューモデル) Wi-Fi">
							<img src="https://ws-fe.amazon-adsystem.com/widgets/q?_encoding=UTF8&ASIN=B00QJDOM6U&Format=_FMjpg_SL80_&ID=AsinImage&MarketPlace=JP&ServiceVersion=20070822&WS=1&tag=hametuha-22" style="vertical-align:middle;"/>
						</a>
						<img src="https://ir-jp.amazon-adsystem.com/e/ir?t=hametuha-22&l=msn&o=9&a=B00QJDOM6U" width="1" height="1" border="0" alt="" style="border:none !important; margin:0px !important;" />


						<br/>
						<a href="http://www.amazon.co.jp/gp/feature.html?docId=3078592246">
							Kindle for PC
						</a>
						| <a href="https://geo.itunes.apple.com/jp/app/kindle-ben-dian-zi-shu-ji/id302584613?mt=8">Kindle
							for iOS</a>
						| <a href="https://play.google.com/store/apps/details?id=com.amazon.kindle">Kindle for
							Android</a>
					</p>
				</div>
			</div>
		</div><!-- series__row--amazon -->
	<?php endif; ?>

	<?php get_template_part( 'parts/share', 'big' ); ?>

	<section class="series__row series__row--related" id="series-related">

		<div class="container series__inner">

			<div class="row">
				<div class="col-xs-12">
					<h2 class="series__title--related text-center">
						<small class="series__title--caption">Recommendations</small>
						おすすめ書籍
					</h2>
				</div>
			</div><!-- //.series__title--related -->

			<?php
			hameplate( 'templates/recommendations', '', [
				'excludes' => get_the_ID(),
				'author'   => get_the_author_meta( 'ID' ),
				'fill'     => true,
			] )
			?>

			<div class="mt-1 text-center">
				<a class="btn btn-lg btn-amazon" href="<?php echo home_url( 'kdp' ); ?>">
					<i class="icon-amazon"></i> もっと見る
				</a>
			</div>

		</div>
	</section><!-- //.series__row--related -->

</div><!-- //.series__wrap -->

<?php get_footer(); ?>
