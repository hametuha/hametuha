<?php
/** @var \Hametuha\Rest\Doujin $this */
?>


	<section id="doujin-detail" class="doujin" itemscope itemtype="https://schema.org/Person">

		<meta itemprop="affiliation" content="破滅派"/>

		<div class="doujin__row doujin__row--base">
			<div class="container">

				<div class="row">

					<div class="col-xs-3">
						<?= get_avatar( $this->doujin->ID, 300, '', $this->doujin->display_name, [
							'itemprop' => 'image',
							'class'    => 'doujin__img img-circle avatar',
						] ) ?>
					</div>

					<div class="col-xs-9">

						<h1 class="doujin__name">
							<ruby>
								<span itemprop="name"><?= esc_html( $this->doujin->display_name ) ?></span>
								<rt><?= esc_html( $this->doujin->user_lastname ) ?></rt>
							</ruby>
							<small><?= hametuha_user_role( $this->doujin->ID ) ?></small>
							<?php hametuha_follow_btn( $this->doujin->ID ) ?>
						</h1>

						<div class="doujin__desc">
							<?= wpautop( esc_html( $this->doujin->description ) ) ?>
						</div>

						<hr/>

						<ul class="doujin__links">
							<li>
								<i class="icon-location4"></i>
								<?php if ( $this->doujin->location ) : ?>
									<span itemprop="homeLocation"><?= esc_html( $this->doujin->location ) ?></span>
								<?php else : ?>
									<span class="text-muted">非公開</span>
								<?php endif; ?>
								<?php if ( $this->doujin->birth_place ) : ?>
									<small>（<span
											itemprop="birthPlace"><?= esc_html( $this->doujin->birth_place ) ?></span>出身）
									</small>
								<?php endif; ?>
							</li>
							<li>
								<i class="icon-link"></i>
								<?php
								if ( preg_match( '#^https?://.+#', $this->doujin->user_url ) ) :
									if ( ! $this->doujin->aim ) {
										$site_name = preg_match( '#https?://[a-zA-Z0-9\-._]+#', $this->doujin->user_url, $match )
											? $match[0]
											: trim_long_sentence( $this->doujin->user_url, 20 );
									} else {
										$site_name = $this->doujin->aim;
									}
									?>
									<a target="_blank" href="<?= esc_attr( $this->doujin->user_url ) ?>" itemprop="url">
										<?= esc_html( $site_name ) ?> <i class="icon-"></i>
									</a>
								<?php else : ?>
									<span class="text-muted">Webサイトなし</span>
								<?php endif; ?>
							</li>
							<li>
								<?php if ( $this->doujin->twitter ) : ?>
									<a href="https://twitter.com/<?= esc_attr( $this->doujin->twitter ) ?>"
									   class="twitter-follow-button" data-show-count="false"
									   data-lang="ja">フォロー</a>
								<?php else : ?>
									<i class="icon-twitter"></i>
									<span class="text-muted">なし</span>
								<?php endif; ?>
							</li>
						</ul>
						<hr/>
						<!-- //.doujin__links -->
						<dl class="doujin__favorites">
							<dt><i class="icon-reading"></i> 好きな作家</dt>
							<dd>
								<?= $this->doujin->favorite_authors ? esc_html( $this->doujin->favorite_authors ) : '<span class="text-muted">登録なし</span>' ?>
							</dd>
							<dt><i class="icon-pen5"></i> 好きな言葉</dt>
							<dd>
								<?php if ( $this->doujin->favorite_words ) : ?>
									<?= wpautop( esc_html( $this->doujin->favorite_words ) ) ?>
								<?php else : ?>
									<p class="text-muted">登録なし</p>
								<?php endif; ?>
							</dd>
						</dl>

					</div>
				</div>

			</div>

		</div>
		<!-- //.doujin_row--base -->

		<div class=" doujin__row doujin__row--activity">
			<div class="container">
				<div class="row">
					<div class="col-sm-4 col-xs-12 doujin__item">
						<h2 class="doujin__item--title text-center">最近の活動</h2>
						<ul class="doujin__activities">
							<?php foreach ( $this->author->get_activities( $this->doujin->ID ) as $activity ) : ?>
								<li class="doujin__activity">
									<?php
									switch ( $activity->type ) {
										case 'comment':
											switch ( get_comment_type( $activity->post_id ) ) {
												case 'review':
													$url = get_permalink( $activity->parent_id );
													$verb = 'レビューを送りました';
													break;
												default:
													$url   = get_comment_link( $activity->post_id );
													$verb = 'コメントしました';
													break;
											}
											$title = sprintf( '%s「%s」に%s',
												get_post_type_object( get_post_type( $activity->parent_id ) )->label,
												get_the_title( $activity->parent_id ),
												$verb
											);
											break;
										case 'review':
											$url   = get_permalink( $activity->post_id );
											$title = sprintf( '「%s」を高く評価しました',
												get_the_title( $activity->post_id )
											);
											break;
										default:
											$url = get_permalink( $activity->post_id );
											switch ( $activity->type ) {
												case 'newsletter':
													$action = 'メール送信';
													break;
												case 'faq':
													$action = 'よくある質問に追加';
													break;
												case 'thread':
													$action = 'スレ立て';
													break;
												case 'announcement':
													$action = '告知';
													break;
												default:
													$action = '投稿';
													break;
											}
											$title = sprintf( '「%s」を%sしました。', get_the_title( $activity->post_id ), $action );
											break;
									}
									?>
									<a href="<?= esc_url( $url ) ?>">
										<span><?= esc_html( $title ) ?></span>
										<small
											class="label label-default"><?= hametuha_passed_time( $activity->date ) ?></small>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
					<div class="col-sm-4 col-xs-12 doujin__item">
						<h2 class="doujin__item--title text-center">統計</h2>
						<dl class="dl-horizontal">
							<dt>活動日数</dt>
							<dd><?= number_format( $this->author->get_active_days( $this->doujin->ID ) ) ?></dd>
							<dt>作品数</dt>
							<dd><?= number_format( get_author_work_count( $this->doujin->ID ) ) ?></dd>
							<dt>文字数</dt>
							<dd><?= number_format( $this->author->get_letter_count( $this->doujin->ID ) ) ?></dd>
							<dt>スター</dt>
							<dd><?= number_format( $this->author->get_star_count( $this->doujin->ID ) ) ?></dd>
							<dt>SNS戦闘力</dt>
							<dd><?= number_format( $this->author->get_sns_count( $this->doujin->ID ) ) ?></dd>
						</dl>


					</div>
					<div class="col-sm-4 col-xs-12 doujin__item">
						<h2 class="doujin__item--title text-center">レビュー</h2>

						<div id="review-graph" class="doujin__item--chart">

						</div>
					</div>
				</div>

			</div>
		</div>


		<div class="doujin__row doujin__row--recent">
			<div class="container">
				<div class="row">
					<div class="col-sm-12">
						<h2 class="text-center">最新の投稿</h2>
						<?php
						$query = new WP_Query( [
							'post_type'      => 'any',
							'author'         => $this->doujin->ID,
							'posts_per_page' => 3,
							'orderby'        => [ 'date' => 'DESC' ],
							'post_status'    => 'publish',
						] );
						if ( $query->have_posts() ) :
							?>
							<ol class="archive-container media-list">
								<?php
								while ( $query->have_posts() ) {
									$query->the_post();
									get_template_part( 'parts/loop', get_post_type() );
								}
								?>
							</ol>
							<a class="btn btn-primary btn-lg btn-block"
							   href="<?= get_author_posts_url( $this->doujin->ID ) ?>?post_type=any">もっと見る</a>
						<?php else : ?>
							<div class="alert alert-warning">
								投稿がありません
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>

		<?php
		$query = new \WP_Query( [
			'post_type'      => 'series',
			'meta_filter'    => 'kdp',
			'author'         => $this->doujin->ID,
			'posts_per_page' => 3,
		] );
		if ( $query->have_posts() ) :
			?>
			<div class="doujin__row doujin__row--kdp">
				<div class="container">
					<div class="row">
						<h2 class="doujin__title--major text-center">Kindle本</h2>
						<ol class="archive-container media-list">
							<?php while ( $query->have_posts() ) : $query->the_post(); ?>
								<?php get_template_part( 'parts/loop', 'series' ); ?>
							<?php endwhile; ?>
						</ol>
						<a class="btn btn-primary btn-lg btn-block"
						   href="<?= get_author_posts_url( $this->doujin->ID ) ?>?meta_filter=kdp&amp;post_type=series">もっと見る</a>
					</div>
				</div>
			</div>
		<?php endif; ?>


		<?php
		$query = new \WP_Query( [
			'post_type'      => 'lists',
			'author'         => $this->doujin->ID,
			'post_status'    => 'publish',
			'posts_per_page' => 3,
		] );
		?>
		<div class="doujin__row doujin__row--list">
			<div class="container">
				<div class="row">
					<h2 class="doujin__title--major text-center">リスト</h2>
					<?php if ( $query->have_posts() ) : ?>
						<ol class="archive-container media-list">
							<?php
							while ( $query->have_posts() ) {
								$query->the_post();
								get_template_part( 'parts/loop', get_post_type() );
							}
							?>
						</ol>
						<a class="btn btn-primary btn-lg btn-block"
						   href="<?= get_author_posts_url( $this->doujin->ID ) ?>?post_type=lists">もっと見る</a>
					<?php else : ?>
						<div class="alert alert-warning">
							投稿がありません
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>

	</section><!-- //#doujin-detail -->

<?php get_template_part( 'parts/share', 'follow' );
