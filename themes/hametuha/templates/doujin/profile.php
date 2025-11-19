<?php
/** @var \Hametuha\Rest\Doujin $this */
$query = new WP_Query( [
	'post_type'      => 'post',
	'author'         => $this->doujin->ID,
	'posts_per_page' => 5,
	'orderby'        => [ 'date' => 'DESC' ],
	'post_status'    => 'publish',
] );
?>


	<section id="doujin-detail" class="doujin">

		<div class="doujin__row doujin__row--base">
			<div class="container">

				<div class="row">

					<div class="col-12 col-sm-3 text-center">
						<?php
						echo get_avatar( $this->doujin->ID, 300, '', $this->doujin->display_name, [
							'class' => 'doujin__img rounded-circle avatar',
						] )
						?>
					</div>

					<div class="col-12 col-sm-9">

						<h1 class="doujin__name">
							<ruby>
								<span><?php echo esc_html( $this->doujin->display_name ); ?></span>
								<rt><?php echo esc_html( $this->doujin->user_lastname ); ?></rt>
							</ruby>
							<small class="badge bg-secondary"><?php echo hametuha_user_role( $this->doujin->ID ); ?></small>

							<?php hametuha_follow_btn( $this->doujin->ID ); ?>

							<?php if ( hametuha_user_allow_contact( $this->doujin->ID ) && $query->have_posts() ) : ?>
								<a class="btn btn-success" href="<?php echo hametuha_user_contact_url( $query->posts[0] ); ?>">問い合わせ</a>
							<?php endif; ?>
						</h1>

						<div class="doujin__desc">
							<?php echo wpautop( esc_html( $this->doujin->description ) ); ?>
						</div>

						<hr/>

						<ul class="doujin__links">
							<li>
								<i class="icon-location4"></i>
								<?php if ( $this->doujin->location ) : ?>
									<span><?php echo esc_html( $this->doujin->location ); ?></span>
								<?php else : ?>
									<span class="text-muted">非公開</span>
								<?php endif; ?>
								<?php if ( $this->doujin->birth_place ) : ?>
									<small>
										<?php echo esc_html( $this->doujin->birth_place ); ?>出身）
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
									<a target="_blank" href="<?php echo esc_attr( $this->doujin->user_url ); ?>" itemprop="url">
										<?php echo esc_html( $site_name ); ?> <i class="icon-"></i>
									</a>
								<?php else : ?>
									<span class="text-muted">Webサイトなし</span>
								<?php endif; ?>
							</li>
							<li>
								<?php if ( $this->doujin->twitter ) : ?>
									<a href="https://twitter.com/<?php echo esc_attr( $this->doujin->twitter ); ?>"
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
								<?php echo $this->doujin->favorite_authors ? esc_html( $this->doujin->favorite_authors ) : '<span class="text-muted">登録なし</span>'; ?>
							</dd>
							<dt><i class="icon-pen5"></i> 好きな言葉</dt>
							<dd>
								<?php if ( $this->doujin->favorite_words ) : ?>
									<?php echo wpautop( esc_html( $this->doujin->favorite_words ) ); ?>
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

		<hr />
		<div class="doujin__row doujin__row--ebooks">
			<div class="container">

				<h2 class="text-center mb-4"><small>Published eBooks</small><br />電子書籍</h2>

				<?php
				hameplate( 'templates/recommendations', '', [
					'author' => $this->doujin->ID,
				] )
				?>
			</div>
		</div>

		<div class=" doujin__row doujin__row--activity">
			<div class="container">
				<div class="row">
					<div class="col-sm-4 col-12 doujin__item">
						<h2 class="doujin__item--title text-center">最新投稿</h2>
						<?php
						if ( $query->have_posts() ) :
							?>
							<ul class="post-list">
								<?php
								while ( $query->have_posts() ) {
									$query->the_post();
									get_template_part( 'parts/loop', 'front', [ 'should_censor' => false ] );
								}
								?>
							</ul>
							<p class="mt-3 text-center">
								<a class="btn btn-primary" href="<?php echo get_author_posts_url( $this->doujin->ID ); ?>">
									<?php printf( __( '%sのすべての作品', 'hametuha' ), esc_html( $this->doujin->display_name ) ); ?>
								</a>
							</p>
						<?php else : ?>
							<div class="alert alert-warning">
								投稿がありません
							</div>
						<?php endif; ?>

					</div>
					<div class="col-sm-4 col-12 doujin__item mt-5 mt-sm-0">
						<h2 class="doujin__item--title text-center">最近の活動</h2>
						<ul class="doujin__activities">
							<?php foreach ( $this->author->get_activities( $this->doujin->ID ) as $activity ) : ?>
								<li class="doujin__activity">
									<?php
									switch ( $activity->type ) {
										case 'comment':
											switch ( get_comment_type( $activity->post_id ) ) {
												case 'review':
													$url  = get_permalink( $activity->parent_id );
													$verb = 'レビューを追加しました';
													break;
												default:
													$url  = get_comment_link( $activity->post_id );
													$verb = 'コメントしました';
													break;
											}
											$label     = '';
											$post_type = get_post_type( $activity->parent_id );
											if ( $post_type ) {
												$label = get_post_type_object( $post_type )->label;
											} else {
												$url = false;
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
										case 'anpi':
											$url = get_permalink( $activity->post_id );
											if ( \Hametuha\Model\Anpis::get_instance()->is_tweet( $activity->post_id ) ) {
												$anpi  = get_post( $activity->post_id );
												$title = trim_long_sentence( $anpi->post_excerpt, 20 );
											} else {
												$title = get_the_title( $activity->post_id );
											}
											$title = sprintf( '安否報告しました： 「%s」', $title );
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
												case 'lists':
													$action = '編纂';
													break;
												case 'idea':
													$action = 'アイデアとしてメモ';
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
									if ( $url ) {
										printf(
											'<a href="%s"><span>%s</span><small class="badge text-bg-secondary">%s</small></a>',
											esc_url( $url ),
											esc_html( $title ),
											esc_html( hametuha_passed_time( $activity->date ) )
										);
									} else {
										printf(
											'<span>%s</span><small class="badge text-bg-secondary">%s</small>',
											esc_html( $title ),
											esc_html( hametuha_passed_time( $activity->date ) )
										);
									}
									?>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
					<div class="col-sm-4 col-12 mt-5 mt-sm-0 doujin__item">
						<h2 class="doujin__item--title text-center">統計</h2>
						<dl class="row">
							<dt class="col-6">活動日数</dt>
							<dd class="col-6"><?php echo number_format( $this->author->get_active_days( $this->doujin->ID ) ); ?></dd>
							<dt class="col-6">作品数</dt>
							<dd class="col-6"><?php echo number_format( get_author_work_count( $this->doujin->ID ) ); ?></dd>
							<dt class="col-6">文字数</dt>
							<dd class="col-6"><?php echo number_format( $this->author->get_letter_count( $this->doujin->ID ) ); ?></dd>
							<dt class="col-6">スター</dt>
							<dd class="col-6"><?php echo number_format( $this->author->get_star_count( $this->doujin->ID ) ); ?></dd>
							<dt class="col-6">SNS戦闘力</dt>
							<dd class="col-6"><?php echo number_format( $this->author->get_sns_count( $this->doujin->ID ) ); ?></dd>
						</dl>
						<h2 class="doujin__item--title text-center">レビュー</h2>
						<div id="review-graph" class="doujin__item--chart"></div>
					</div>
				</div>

			</div>
		</div>

		<?php
		$query = new \WP_Query( [
			'post_type'      => 'lists',
			'author'         => $this->doujin->ID,
			'post_status'    => 'publish',
			'posts_per_page' => 3,
		] );
		if ( $query->have_posts() ) :
			?>
			<div class="doujin__row doujin__row--list">
				<div class="container">
					<h2 class="doujin__title--major text-center">リスト</h2>
					<ol class="archive-container row">
						<?php
						while ( $query->have_posts() ) {
							$query->the_post();
							get_template_part( 'parts/loop', get_post_type() );
						}
						?>
					</ol>
					<p class="mt-3 text-center">
						<a class="btn btn-primary btn-lg" href="<?php echo home_url( "lists/by/{$this->doujin->user_nicename}/" ); ?>">
							もっと見る
						</a>
					</p>
				</div>
			</div>
		<?php endif; ?>

	</section><!-- //#doujin-detail -->
