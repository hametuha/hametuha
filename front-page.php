<?php get_header(); ?>

	<div class="container front-container">

		<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

			<?php if ( has_post_thumbnail() && ( $thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' ) ) ) : ?>
				<div class="row">
					<div class="jumbotron" style="background-image: url('<?= $thumbnail[0] ?>')">
						<h1>後ろ向きのまま前へ進め！</h1>
						<?php the_excerpt() ?>
						<p><a class="btn btn-success btn-lg" href="#about-us">破滅派とは？</a></p>
					</div>
				</div>
			<?php endif; ?>

			<div class="front-page post-content">
				<?php the_content(); ?>
			</div>

		<?php endwhile; endif; ?>
	</div><!-- //.front-container -->

<?php get_template_part( 'parts/list', 'kdp' ) ?>


	<div class="container front-container">
		<div class="frontpage-widget clearfix">

			<?php dynamic_sidebar( 'frontpage-sidebar' ); ?>


			<div class="col-xs-12 col-sm-4">
				<h2>
					人気の投稿
				</h2>
				<small><?= get_latest_ranking_day( get_option( 'date_format' ) ) ?>更新</small>
				<ul class="post-list">
					<?php
					$ranking_query = new WP_Query( [
						'ranking'        => 'last_week',
						'posts_per_page' => 5,
					] );
					while ( $ranking_query->have_posts() ) {
						$ranking_query->the_post();
						get_template_part( 'parts/loop', 'front' );
					}
					wp_reset_postdata();
					?>
				</ul>
				<p>
					<a href="<?= home_url( '/ranking/' ) ?>" class="btn btn-default btn-block">ランキング一覧</a>
				</p>
			</div>


			<?php
			$query = new WP_Query( [
				'my-content'     => 'recommends',
				'posts_per_page' => 1,
				'post_type'      => 'lists',
				'post_status'    => 'publish',
			] );
			if ( $query->have_posts() ) : $query->the_post();
				$url = get_permalink();
				?>
				<div class="col-xs-12 col-sm-4">
					<h2>
						編集部オススメ
					</h2>
					<small><?= the_date() ?>更新</small>
					<ul class="post-list">
						<?php
						$sub_query = new WP_Query( [
							'post_type'      => 'in_list',
							'post_status'    => 'publish',
							'post_parent'    => get_the_ID(),
							'posts_per_page' => '3',
						] );
						while ( $sub_query->have_posts() ) {
							$sub_query->the_post();
							get_template_part( 'parts/loop', 'front' );
						}
						?>
					</ul>
					<p>
						<a href="<?= $url ?>" class="btn btn-default btn-block">もっと見る</a>
					</p>
				</div>

				<?php wp_reset_postdata(); endif; ?>


			<div class="col-xs-12 col-sm-4">
				<h2>新着投稿</h2>
				<ul class="post-list">
					<?php
					foreach ( hametuha_recent_posts( 5 ) as $post ) {
						setup_postdata( $post );
						get_template_part( 'parts/loop', 'front' );
					}
					wp_reset_postdata();
					?>
				</ul>
				<p>
					<a href="<?= home_url( '/latest/' ) ?>" class="btn btn-default btn-block">すべての新着投稿</a>
				</p>
			</div>

			<div class="col-xs-12 col-sm-4">
				<h2>検索</h2>

				<p class="text-muted">
					よくわからなかったら検索してみよう！
				</p>
				<?php get_search_form() ?>
			</div>


			<div class="col-xs-12 col-sm-4">
				<h2>掲示板</h2>

				<div class="list-group">
					<?php foreach (
						get_posts( [
							'post_type'      => 'thread',
							'posts_per_page' => 3,
						] ) as $post
					) : setup_postdata( $post ); ?>
						<a class="list-group-item" href="<?php the_permalink(); ?>">
							<h3 class="list-group-item-heading">
								<?= hametuha_censor( get_the_title() ); ?>
								<span class="badge"><?php comments_number( '0', '1', '%' ); ?></span>
								<?php if ( is_new_post( 7, $post ) ) : ?>
									<span class="label label-danger">New</span>
								<?php endif; ?>
							</h3>

							<p class="list-group-item-text">
								<?php foreach ( get_the_terms( $post, 'topic' ) as $term ) : ?>
									<span class="label label-info"><?= esc_html( $term->name ) ?></span>
								<?php endforeach; ?>
								<?php the_author() ?>
								（<?php echo human_time_diff( strtotime( get_the_time( 'Y-m-d H:i:s' ) ) ); ?>前）
							</p>
						</a>
					<?php endforeach;
					wp_reset_postdata(); ?>
				</div>
				<p>
					<a href="<?= get_post_type_archive_link( 'thread' ); ?>"
					   class="btn btn-default btn-block">掲示板トップ</a>
				</p>
			</div>

			<div class="col-xs-12 col-sm-4">
				<h2>お知らせ</h2>

				<div class="list-group">
					<?php
					$announcement = new WP_Query( [
						'post_type'      => 'announcement',
						'posts_per_page' => 3,
						'post_status'    => 'publish',
					] );
					while ( $announcement->have_posts() ) :
						$announcement->the_post();
						?>
						<a class="list-group-item" href="<?php the_permalink() ?>">
							<h3 class="list-group-item-heading"><?php the_title() ?></h3>

							<p>
								<?php the_date() ?>
								<?php if ( is_new_post( 7 ) ) : ?>
									<span class="label label-danger">New</span>
								<?php endif; ?>
							</p>
						</a>
					<?php endwhile;
					wp_reset_postdata() ?>
				</div>
				<p>
					<a href="<?= get_post_type_archive_link( 'announcement' ) ?>" class="btn btn-default btn-block">お知らせ一覧</a>
				</p>
			</div>


			<div class="col-xs-12 col-sm-4">
				<h2>統計情報</h2>
				<script>
					window.HametuhaGenreStatic = <?= json_encode( hametuha_genre_static() ) ?>;
				</script>
				<canvas id="genre-context" width="300" height="300"></canvas>
				<p class="list-excerpt">
					<?= date_i18n( 'Y年n月j日' ); ?>現在、破滅派には<a
						href="<?= home_url( '/authors/'); ?>"><?= number_format_i18n( get_author_count() ); ?>
						人</a>の同人が参加し、
					<a href="<?php echo home_url( '/latest/' ); ?>"><?= number_format_i18n( get_current_post_count() ); ?>
						作品</a>が登録されています。
				</p>
			</div>


			<div class="col-xs-12 col-sm-4">
				<h2>新着シリーズ</h2>
				<ul class="post-list">
					<?php
					foreach ( hametuha_recent_series( 3 ) as $post ) {
						setup_postdata( $post );
						get_template_part( 'parts/loop', 'front' );
					}
					wp_reset_postdata();
					?>
				</ul>
				<p>
					<a href="<?= get_post_type_archive_link( 'series' ) ?>" class="btn btn-default btn-block">シリーズ一覧</a>
				</p>
			</div>


			<div class="col-xs-12 col-sm-4">
				<h2>新人さん</h2>
				<ul class="user-list">
					<?php $counter = 0;
					foreach ( get_recent_authors( 3 ) as $user ) : $counter ++; ?>
						<li class="clearfix">
							<?php echo get_avatar( $user->ID, 80 ); ?>
							<div class="user-info">
								<a href="<?= home_url( sprintf( '/doujin/detail/%s/', $user->user_nicename ) ); ?>">
									<h3>
										<?= esc_html( $user->display_name ); ?>
										<small><?php echo mysql2date( 'Y/m/d', $user->user_registered ); ?>登録</small>
									</h3>
								</a>
							</div>
							<p class="list-excerpt">
								最新投稿:
								<a href="<?php echo get_permalink( $user->post_id ); ?>">
									<?php echo $user->post_title; ?>
								</a>
							</p>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>


			<div class="col-xs-12 col-sm-4">
				<h2>最近がんばってる</h2>
				<?php $counter     = 0;
				$activity_interval = 7;
				$vigorous          = get_vigorous_author( $activity_interval, 3 );
				if ( empty( $vigorous ) ) : ?>
					<div class="alert alert-danger">
						<p>ここ<?php echo number_format( $activity_interval ); ?>日間というもの、誰も活動していません！　あなたの力が必要です。</p>

						<p>

							<?php if ( ! is_user_logged_in() ) : ?>
								<a class="btn btn-danger btn-block" href="<?= wp_login_url() ?>">ログインして書く</a>
							<?php elseif ( current_user_can( 'edit_posts' ) ) : ?>
								<a class="btn btn-danger btn-block" href="<?= admin_url( 'post-new.php' ) ?>">作品を書く</a>
							<?php endif; ?>
						</p>
					</div>
				<?php else : ?>
					<ul class="user-list">
						<?php foreach ( $vigorous as $user ) : $counter ++; ?>
							<li class="clearfix">
								<?php if ( 1 === $counter ) : ?>
									<i class="icon-crown"></i>
								<?php endif; ?>

								<?= get_avatar( $user->ID, 80 ); ?>
								<div class="user-info">
									<h3>
										<a href="<?= home_url( sprintf( '/doujin/detail/%s', $user->user_nicename ) ); ?>">
											<?= esc_html( $user->display_name ); ?>
										</a>
										<small><?php echo mysql2date( 'Y/m/d', $user->user_registered ); ?>登録</small>
									</h3>
								</div>
								<p class="list-excerpt">
									<?= $activity_interval ?>日間で<?= number_format_i18n( $user->length ) ?>文字を書きました。
								</p>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>

			<div class="col-xs-12 col-sm-4">
				<h2>一番投稿した</h2>
				<ul class="user-list">
					<?php $counter = 0;
					foreach ( get_vigorous_author( 0, 3 ) as $user ) : $counter ++; ?>
						<li class="clearfix">
							<?php if ( 1 === $counter ) : ?>
								<i class="icon-crown"></i>
							<?php endif; ?>

							<?= get_avatar( $user->ID, 80 ); ?>
							<div class="user-info">
								<h3>
									<a href="<?= home_url( sprintf( '/doujin/detail/%s', $user->user_nicename ) ); ?>">
										<?= esc_html( $user->display_name ); ?>
									</a>
									<small><?php echo mysql2date( 'Y/m/d', $user->user_registered ); ?>登録</small>
								</h3>
							</div>
							<p class="list-excerpt">
								これまでに<?= number_format_i18n( $user->length ) ?>文字を書きました。
							</p>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>


			<div class="col-xs-12 col-sm-4">
				<h2>人気のタグ</h2>

				<p class="tag-cloud">
					<?php wp_tag_cloud(); ?>
				</p>
			</div>

			<div class="col-xs-12 col-sm-4 widget-twitter">
				<a class="twitter-timeline" href="https://twitter.com/hashtag/%E7%A0%B4%E6%BB%85%E6%B4%BE" data-widget-id="727361413681836032">
					#破滅派 のツイート
				</a>
			</div>

			<div class="col-xs-12 col-sm-4 widget-facebook">
				<div class="fb-page" data-href="https://www.facebook.com/hametuha.inc" data-height="230"
				     data-small-header="false" data-adapt-container-width="true" data-hide-cover="false"
				     data-show-facepile="true" data-show-posts="false">
					<div class="fb-xfbml-parse-ignore">
						<blockquote cite="https://www.facebook.com/hametuha.inc"><a
								href="https://www.facebook.com/hametuha.inc">株式会社破滅派</a></blockquote>
					</div>
				</div>
			</div>

		</div>

		<div id="about-us" class="clearfix">

			<h2 class="page-header text-center">
				破滅派ってなあに？<br/>
				<small>はじめての方へ</small>
			</h2>

			<div class="col-sm-4 col-xs-12">
				<p class="icon">
					<i class="icon-ha"></i>
				</p>

				<div class="caption">
					<h3>破滅派を知ろう</h3>

					<p>
						破滅派は要するに<strong>オンライン文芸誌</strong>であり、文学作品を発表したり、読んだりできます。<br/>
						<a href="<?= home_url( '/about/' ) ?>">設立の経緯</a>や<a
							href="<?= home_url( '/history/' ) ?>">活動の記録</a>などをご覧頂き、
						恐れを消してください。
					</p>
				</div>
			</div>

			<div class="col-sm-4 col-xs-12">
				<p class="icon">
					<i class="icon-reading"></i>
				</p>

				<div class="caption">
					<h3>破滅派に関わろう</h3>

					<p>
						破滅派はオンライン文芸誌なので、詩や小説といった文学作品を掲載することができます。
						新たな読者との出会いがあなたを待っています。<br/>
						「自分は作品を書けないな」という方でも、レビューを残したり、掲示板に書き込んだり、色々な楽しみ方ができます。
					</p>
				</div>
			</div>

			<div class="col-sm-4 col-xs-12">
				<p class="icon">
					<i class="icon-enter"></i>
				</p>

				<div class="caption">
					<h3>まずは新規登録</h3>

					<p>
						なにはともあれ、破滅派にログインしましょう。<br/>
						破滅派にアカウントを作成するのに必要なのはメールアドレスだけ。TwitterやFacebookのアカウントでも登録できます。
					</p>
				</div>
			</div>

		</div>

		<?php if ( is_user_logged_in() ) : ?>
			<?php if ( current_user_can( 'edit_posts' ) ) : ?>
				<a class="btn btn-lg btn-block btn-primary btn--joinus"
				   href="<?= admin_url( 'post-new.php' ) ?>">作品を書く</a>
			<?php else : ?>
				<a class="btn btn-lg btn-block btn-primary btn--joinus"
				   href="<?= admin_url( 'post-new.php' ) ?>">執筆者になる</a>
			<?php endif; ?>
		<?php else : ?>
			<p>
				<a class="btn btn-lg btn-block btn-primary btn--joinus" href="<?= wp_login_url() ?>">破滅派にログイン</a>
			</p>
		<?php endif; ?>



		<?php get_template_part( 'parts/share' ) ?>


	</div><!-- front-container -->

<?php get_footer(); ?>