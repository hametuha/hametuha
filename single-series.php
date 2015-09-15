<?php get_header() ?>

<?php get_header( 'breadcrumb' ) ?>

<div class="series__wrap" itemscope itemtype="http://schema.org/Book">

	<?php the_post();
	$series = \Hametuha\Model\Series::get_instance();
	$query  = new WP_Query( [
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'post_parent'    => get_the_ID(),
		'posts_per_page' => - 1,
		'orderby'        => [
			'menu_order' => 'DESC',
			'date'       => 'ASC',
		],
		'paged'          => max( 1, intval( get_query_var( 'paged' ) ) ),
	] );
	?>

	<div class="series__row series__row--cover">
		<div class="container series__inner" id="series-<?php the_ID() ?>">

			<meta itemprop="bookFormat" content="EBook">

			<div class="row series__meta">
				<div class="col-xs-12 col-sm-4 series__meta--thumbnail text-center">
					<?php if ( has_post_thumbnail() ) : ?>
						<?php the_post_thumbnail( 'medium', [
							'itemprop' => 'image',
						] ); ?>
					<?php else : ?>
						<img itemprop="image" src="<?= get_stylesheet_directory_uri() ?>/assets/img/covers/printing.png"
							 alt="Now Printing..." width="300" height="480"/>
					<?php endif; ?>
				</div>

				<div class="col-xs-12 col-sm-8 series__top">

					<!-- title -->
					<div class="series__header">
						<h1 class="series__title">
							<span itemprop="name"><?php the_title(); ?></span>
							<?php if ( ( $subtitle = $series->get_subtitle( $post->ID ) ) ) : ?>
								<small itemprop="headline">
									<?= esc_html( $subtitle ) ?>
								</small>
							<?php endif; ?>
						</h1>
					</div>
					<!-- //.series__header -->

					<?php if ( has_excerpt() ) : ?>

						<div class="series__excerpt" itemprop="description">
							<?php the_excerpt(); ?>
						</div><!-- //.excerpt -->

					<?php endif; ?>

					<div class="series__link text-center">
						<?php switch ( $series->get_status( get_the_ID() ) ) :
							case 2 : ?>
								<a href="<?= $series->get_kdp_url( get_the_ID() ); ?>" class="btn btn-trans btn-lg btn-amazon"
								   data-outbound="kdp"
								   data-action="<?= esc_attr( $series->get_asin( get_the_ID() ) ) ?>"
								   data-label="<?php the_ID() ?>"
								   data-value="<?= get_series_price() ?>">
									<i class="icon-amazon"></i> Amazonで見る
								</a>
								<?php break;
							case 1 : ?>
								<a href="#series-notification" class="btn btn-trans btn-lg">
									<i class="icon-info2"></i> 販売準備中
								</a>
								<?php break;
							default : ?>
								<?php break; endswitch; ?>
						<span class="series__link--divider"></span>
						<a href="#series-children" class="btn btn-trans">
							<i class="icon-books"></i> 掲載作一覧
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
						&yen; <strong><?php the_series_price() ?></strong>
					</p>
				<?php endif; ?>
				<ol class="series__status">
					<li>
						<?php
						$range = $series->get_series_range( get_the_ID() );
						if ( $series->is_finished( get_the_ID() ) ) : ?>
							<i class="icon-checkmark3 ok"></i> 完結済み
							（
							<?= mysql2date( get_option( 'date_format' ), $range->start_date ) ?>
							〜
							<?= mysql2date( get_option( 'date_format' ), $range->last_date ) ?>
							）
						<?php else : ?>
							<i class="icon-info2 ng"></i> 連載中
							<small>
								（最終更新： <?= mysql2date( get_option( 'date_format' ), $range->last_date ) ?>
								）
							</small>
						<?php endif; ?>
					</li>
					<li>
						<i class="icon-books ok"></i> <?= number_format( $query->post_count ) ?> 作品収録
					</li>
					<li>
						<?php the_post_length( '<i class="icon-reading ok"></i> ', '文字', '<i class="icon-reading ng"></i> 文字数不明' ) ?>
					</li>
					<?php
					$afterwords = trim( $post->post_content );
					if ( ! empty( $afterwords ) ) : ?>
						<li>
							<i class="icon-file-plus ok"></i>
							あとがき付き（約<?= number_format( mb_strlen( $post->post_content, 'utf-8' ) ) ?>文字）
						</li>
					<?php endif; ?>
				</ol>
			</div>
		</div>
	</div>

	<div class="series__row series__row--author">

		<div class="container series__inner">

			<div class="row">
				<div class="col-xs-12">
					<h2 class="series__title--author text-center">
						<small class="series__title--caption">Authors &amp; Editors</small>
						執筆者・編集者
					</h2>
				</div>
			</div>

			<?php
			$editor   = new WP_User( $post->post_author );
			$authors  = $series->get_authors( get_the_ID() );
			$existent = 0;
			foreach ( $authors as &$author ) {
				if ( $author->ID != $editor->ID ) {
					$existent ++;
				}
				$author->editor = false;
			}
			if ( count( $authors ) === $existent ) {
				$editor->editor = true;
				$authors[]      = $editor;
			}
			foreach ( $authors as $author ) :
				?>

				<div class="row series__author">

					<div class="series__author--photo col-xs-4 text-center">
						<?= get_avatar( $author->ID, '150', '', $author->display_name, [ 'class' => 'img-circle' ] ) ?>
					</div>
					<!-- //.series__author -->

					<div class="series__author--profile col-xs-8">
						<h3>
							<span><?= esc_html( $author->display_name ) ?></span>
							<small><?= $author->editor ? '編集' : '執筆' ?></small>
						</h3>

						<div class="series__author--desc">
							<?= wpautop( get_user_meta( $author->ID, 'description', true ) ) ?>
						</div>

						<div class="series__authorLink">
							<a class="btn btn-default" href="<?= get_author_posts_url( $author->ID ) ?>">詳しいプロフィール</a>
						</div>
					</div>
					<!-- //.series__author -->

				</div><!-- //.row -->

			<?php endforeach; ?>

		</div>
		<!-- //.container -->

	</div>
	<!-- //.series__row--author -->


	<div class="series__row series__row--children" id="series-children">

		<div class="container series__inner">

			<div class="row">
				<div class="col-xs-12">
					<h2 class="series__title--list text-center">
						<small class="series__title--caption">Works</small>
						掲載作一覧
					</h2>
				</div>
			</div>

			<?php
			if ( $query->have_posts() ) :

				?>

				<ol class="series__list row masonry-list">
					<?php
					$counter = 0;
					while ( $query->have_posts() ) {
						$query->the_post();
						get_template_part( 'parts/loop-series', get_post_type() );
					}
					wp_reset_postdata();
					?>
				</ol>

			<?php else : ?>

				<div class="alert alert-warning">
					<p>まだ作品が登録されていません。<a class="alert-link" href="#series-notification">破滅派をフォロー</a>して、作者の活躍に期待してください。</p>
				</div>

			<?php endif;
			wp_reset_postdata(); ?>
		</div>
		<!-- //.container -->

	</div>
	<!-- series_row--children -->

	<?php if ( $url = $series->get_kdp_url( get_the_ID() ) ) : ?>
		<div class="series__row series__row--amazon">
			<div class="row">
				<div class="col-xs-12">

					<h2 class="series__title--share text-center">
						<small class="series__title--caption">Buy at Amazon</small>
						購入する
					</h2>
					<p class="series__price text-center">
						&yen; <strong><?php the_series_price() ?></strong>
					</p>

					<p class="text-muted text-center">
						AmazonのKindleストアで購入できます。
					</p>

					<p class="text-center">
						<a href="<?= $url ?>" class="btn btn-trans btn-lg btn-amazon"
						   data-outbound="kdp"
						   data-action="<?= esc_attr( $series->get_asin( get_the_ID() ) ) ?>"
						   data-label="<?php the_ID() ?>"
						   data-value="<?= get_series_price() ?>">
							<i class="icon-amazon"></i> この本を購入する
						</a>
					</p>

					<p class="text-muted text-center">
						<strong>※ Kindle以外にもスマートフォンやPCの無料アプリで読めます。</strong><br/>
						<iframe
							src="http://rcm-fe.amazon-adsystem.com/e/cm?t=hametuha-22&o=9&p=21&l=ur1&category=kindlerotate&f=ifr"
							width="125" height="125" scrolling="no" border="0" marginwidth="0" style="border:none;"
							frameborder="0"></iframe>
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

	<?php get_template_part( 'parts/share', 'big' ) ?>

	<?php get_template_part( 'parts/share', 'follow' ) ?>

</div><!-- //.series__wrap -->

<?php get_footer(); ?>
