<?php
/**
 * フロントページテンプレート
 *
 */
get_header(); ?>


<?php
the_post();
$style = '';
if ( has_post_thumbnail() && ( $thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' ) ) ) {
	$style = "background-image: url('{$thumbnail[0]}')";
}
?>
	<div class="front-container" style="<?php echo $style; ?>">
		<div class="container">
			<div class="front-container__hero pt-5 pb-5">
				<h1 class="front-container__hero--title">後ろ向きのまま前へ進め！</h1>
				<div class="front-container__hero--excerpt h5">
					<?php the_excerpt(); ?>
				</div>
				<p class="front-container__hero--buttons">
					<a class="btn btn-outline-light" href="#about-us">破滅派とは？</a>
					<?php if ( ! is_user_logged_in() ) : ?>
						<a class="btn btn-outline-light" style="margin-left: 1em;"
							href="<?php echo wp_registration_url(); ?>" rel="nofollow">登録する</a>
					<?php elseif ( ! current_user_can( 'edit_posts' ) ) : ?>
						<a class="btn btn-outline-light" style="margin-left: 1em;"
							href="<?php echo home_url( 'become-author' ); ?>" rel="nofollow">同人になる</a>
					<?php endif; ?>
				</p>
			</div>
		</div><!-- //.front-container -->
	</div>

<?php get_header( 'sub' ); ?>

<?php
/**
 * 告知エリア
 */
?>
	<div class="container">
		<nav class="front-announcement row align-items-center">
			<ul class="col-10 front-announcement__list">
				<?php
				$announcement = new WP_Query( [
					'post_type'      => 'announcement',
					'posts_per_page' => 3,
					'post_status'    => 'publish',
				] );
				while ( $announcement->have_posts() ) :
					$announcement->the_post();
					?>
					<li class="front-announcement__item">
						<a class="d-flex justify-content-start" href="<?php the_permalink(); ?>">
							<time class="date d-inline-block" datetime="<?php echo the_time( DATETIME::ATOM ); ?>">
								<strong>【お知らせ】</strong><strong
									class="d-none d-sm-inline"><?php the_date(); ?></strong>
							</time>
							<span><?php the_title(); ?></span>
						</a>
					</li>
					<?php
				endwhile;
				wp_reset_postdata()
				?>
			</ul>
			<p class="col-2 mb-0">
				<a href="<?php echo get_post_type_archive_link( 'announcement' ); ?>" class="btn btn-secondary w-100">
					<span class="d-none d-sm-inline">お知らせ一覧</span> <i class="icon-arrow-right6"></i>
				</a>
			</p>
		</nav>
	</div>

<?php
// 公募エリア
$campaigns = hametuha_recent_campaigns( 2 );
if ( $campaigns && ! is_wp_error( $campaigns ) ) :
	// 件数に応じてクラスを切り替え
	$campaign_count = count( $campaigns );
	$col_class      = ( 1 === $campaign_count ) ? 'col-12' : 'col-12 col-sm-6';
	?>
	<section class="front-campaign mt-3 mb-3">
		<div class="container">
			<div class="row">
				<?php foreach ( $campaigns as $campaign ) : ?>
					<div class="mb-3 <?php echo esc_attr( $col_class ); ?>">
						<a href="<?php echo esc_html( get_term_link( $campaign ) ); ?>"
							class="d-flex justify-content-between align-items-center front-campaign__link">
							<span>
								<small>【公募】</small><br />
								<span class="front-campaign__title"><?php echo esc_html( $campaign->name ); ?></span>
								<?php
								$limit = get_term_meta( $campaign->term_id, '_campaign_limit', true );
								?>
								<span class="front-campaign__limit">
									<?php esc_html_e( '〆切: ', 'hametuha' ); ?>
									<time date="<?php echo esc_html( $limit ); ?>">
										<?php echo mysql2date( get_option( 'date_format' ), $limit ); ?>
									</time>
								</span>
							</span>

							<?php if ( hametuha_is_available_campaign( $campaign ) ) : ?>
								<span class="badge text-bg-danger">募集中</span>
							<?php else : ?>
								<span class="badge text-bg-secondary">終了</span>
							<?php endif; ?>
						</a>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php
// ジャンル
$post_list_cache = get_transient( 'front_posts_cache' );
if ( false !== $post_list_cache ) :
	echo $post_list_cache;
else :
	ob_start();
	?>
	<div class="container front-container pb-5">
		<div class="row">

			<div class="col-12 col-sm-4 front-page-widget">
				<h2>
					人気の投稿
				</h2>
				<small><?php echo get_latest_ranking_day( get_option( 'date_format' ) ); ?>更新</small>
				<ul class="post-list">
					<?php
					$ranking_query = new WP_Query( [
						'ranking'        => 'last_week',
						'posts_per_page' => 3,
					] );
					while ( $ranking_query->have_posts() ) {
						$ranking_query->the_post();
						get_template_part( 'parts/loop', 'front' );
					}
					wp_reset_postdata();
					?>
				</ul>
				<p>
					<a href="<?php echo home_url( '/ranking/' ); ?>"
						class="btn btn-outline-secondary w-100">ランキング一覧</a>
				</p>
			</div>

			<?php
			$query = new WP_Query( [
				'my-content'     => 'recommends',
				'posts_per_page' => 1,
				'post_type'      => 'lists',
				'post_status'    => 'publish',
			] );
			if ( $query->have_posts() ) :
				$query->the_post();
				$url = get_permalink();
				?>
				<div class="col-12 col-sm-4 front-page-widget">
					<h2>
						<?php the_title(); ?>
					</h2>
					<small><?php echo the_date(); ?>更新</small>
					<ul class="post-list">
						<?php
						$sub_query = new WP_Query( [
							'post_type'      => 'post',
							'post_status'    => 'publish',
							'posts_per_page' => 3,
							'in_list'        => get_the_ID(),
						] );
						while ( $sub_query->have_posts() ) {
							$sub_query->the_post();
							get_template_part( 'parts/loop', 'front' );
						}
						?>
					</ul>
					<p>
						<a href="<?php echo $url; ?>" class="btn btn-outline-secondary w-100">もっと見る</a>
					</p>
				</div>
				<?php
				wp_reset_postdata();
			endif;
			?>

			<div class="col-12 col-sm-4 front-page-widget">
				<h2>
					<?php esc_html_e( '最近の高評価', 'hametuha' ); ?>
				</h2>
				<ul class="post-list">
					<?php
					$sub_query = new WP_Query( [
						'post_type'      => 'post',
						'post_status'    => 'publish',
						'posts_per_page' => '3',
						'rating'         => 4,
					] );
					while ( $sub_query->have_posts() ) {
						$sub_query->the_post();
						get_template_part( 'parts/loop', 'front' );
					}
					?>
				</ul>
				<p>
					<a href="<?php echo $url; ?>" class="btn btn-outline-secondary w-100">もっと見る</a>
				</p>
			</div>


			<div class="col-12 col-sm-4 front-page-widget">
				<h2>
					<?php esc_html_e( 'サクッと読める短編', 'hametuha' ); ?>
				</h2>
				<ul class="post-list">
					<?php
					$sub_query = new WP_Query( [
						'post_type'          => 'post',
						'post_status'        => 'publish',
						'posts_per_page'     => '3',
						'length'             => 'short',
						'author_not_flagged' => 'spam',
					] );
					while ( $sub_query->have_posts() ) {
						$sub_query->the_post();
						get_template_part( 'parts/loop', 'front' );
					}
					?>
				</ul>
				<p>
					<a href="<?php echo $url; ?>" class="btn btn-outline-secondary w-100">もっと見る</a>
				</p>
			</div>

			<div class="col-12 col-sm-4 front-page-widget">
				<h2>
					<?php esc_html_e( '完結済み', 'hametuha' ); ?>
				</h2>
				<ul class="post-list">
					<?php
					$sub_query = new WP_Query( [
						'post_type'      => 'series',
						'post_status'    => 'publish',
						'posts_per_page' => '3',
						'meta_query'     => [
							[
								'key'   => '_series_finished',
								'value' => '1',
							],
						],
					] );
					while ( $sub_query->have_posts() ) {
						$sub_query->the_post();
						get_template_part( 'parts/loop', 'front' );
					}
					?>
				</ul>
				<p>
					<a href="<?php echo $url; ?>" class="btn btn-outline-secondary w-100">もっと見る</a>
				</p>
			</div>

			<?php if ( $recent_posts = hametuha_recent_posts( 5 ) ) : ?>
				<div class="col-12 col-sm-4 front-page-widget">
					<h2>新着投稿</h2>
					<ul class="post-list">
						<?php
						foreach ( $recent_posts as $post ) {
							setup_postdata( $post );
							get_template_part( 'parts/loop', 'front' );
						}
						wp_reset_postdata();
						?>
					</ul>
					<p>
						<a href="<?php echo home_url( '/latest/' ); ?>"
							class="btn btn-outline-secondary w-100">すべての新着投稿</a>
					</p>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<?php
	$content = ob_get_flush();
	set_transient( 'front_posts_cache', $content, HOUR_IN_SECONDS );
endif;

// ジャンル
$genre_cache = get_transient( 'front_genre_cache' );
if ( false !== $genre_cache ) :
	echo $genre_cache;
else :
	ob_start();
	?>
	<section class="front-search front-container front-container--inverse">
		<div class="container pt-5 pb-5">
			<div class="row">

				<div class="col-12 col-sm-4 front-page-widget">
					<h2><?php esc_html_e( '作品の形式', 'hametuha' ); ?></h2>
					<ul class="post-list">
						<?php
						$categories = get_categories( [ 'orderby' => 'count', 'order' => 'DESC' ] );
						foreach ( $categories as $category ) :
							?>
							<li>
								<a href="<?php echo get_term_link( $category ); ?>">
									<h3 class="list-heading d-flex justify-content-between">
										<span><?php echo esc_html( $category->name ); ?></span>
										<small class="list-heading-category">
											<?php printf( '%s件', number_format( $category->count ) ); ?>
										</small>
									</h3>
									<div class="list-excerpt">
										<?php echo esc_html( $category->description ); ?>
									</div>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>

				<div class="col-12 col-sm-4 front-page-widget">
					<h2><?php esc_html_e( '感想', 'hametuha' ); ?></h2>
					<?php
					$reviews = \Hametuha\Model\Review::get_instance();
					foreach ( $reviews->feedback_tags as $genre => list( $positive, $negative ) ) :
						?>
						<h3 class="front-review__label text-center mb-3"><?php echo esc_html( $reviews->review_tag_label( $genre ) ); ?></h3>
						<div class="row mb-3">
							<div class="col-6">
								<a class="btn btn-outline-info w-100" href="<?php echo esc_url( home_url( sprintf( '/reaction/%s/', rawurlencode( $positive ) ) ) ); ?>">
									<?php echo esc_html( $positive ); ?>
								</a>
							</div>
							<div class="col-6">
								<a class="btn btn-outline-danger w-100" href="<?php echo esc_url( home_url( sprintf( '/reaction/%s/', rawurlencode( $negative ) ) ) ); ?>">
									<?php echo esc_html( $negative ); ?>
								</a>
							</div>
						</div>
					<?php endforeach; ?>
				</div>

				<div class="col-12 col-sm-4 front-page-widget">
					<h2>人気のタグ</h2>
					<p class="tag-cloud">
						<?php
						$tags = hametuha_get_popular_recent_tags( 30, 10, 20 );
						if ( ! empty( $tags ) ) {
							foreach ( $tags as $tag ) {
								$font_size = 12 + min( 10, floor( $tag->count / 10 ) ); // 件数に応じてフォントサイズを調整
								printf(
									'<a href="%s" class="tag-link" style="font-size: %dpx;">#%s（%d）</a> ',
									esc_url( get_tag_link( $tag ) ),
									$font_size,
									esc_html( $tag->name ),
									$tag->count
								);
							}
						} else {
							wp_tag_cloud();
						}
						?>
					</p>
				</div>
			</div>

			<div>
				<?php get_search_form(); ?>
			</div>
		</div>
	</section>
	<?php
	$genre_cache = ob_get_contents();
	ob_end_flush();
	set_transient( 'front_genre_cache', $genre_cache, DAY_IN_SECONDS );
endif;

// 活動
$activities = get_transient( 'hametuha_front_activities' );
if ( false !== $activities ) :
	echo $activities;
else :
	ob_start();
	?>
	<div class="container front-container pb-5 pt-5">

		<h2 class="page-header text-center mb-5" style="border-bottom: none;">
			<small>Activities</small>
			<br />
			<?php esc_html_e( '同人の活動', 'hametuha' ); ?>
		</h2>

		<div class="row">
			<div class="col-12 col-sm-4 front-page-widget">
				<h2><?php esc_html_e( '最近のコメント', 'hametuha' ); ?></h2>
				<ul class="post-list">
					<?php
					// 多めに取得して重複を省く
					$comments_query  = new WP_Comment_Query( [
						'post_type' => 'post',
						'status'    => 'approve',
						'type'      => 'comment',
						'number'    => 100,
						'orderby'   => 'comment_date_gmt',
						'order'     => 'DESC',
					] );
					$displayed_posts = [];
					$comment_count   = 0;
					foreach ( $comments_query->get_comments() as $comment ) {
						// 同じ投稿のコメントは1つだけ
						if ( in_array( $comment->comment_post_ID, $displayed_posts, true ) ) {
							continue;
						}
						$displayed_posts[] = $comment->comment_post_ID;
						get_template_part( 'parts/loop-front', 'comment', [ 'comment' => $comment ] );
						++$comment_count;
						// 3件表示したら終了
						if ( $comment_count >= 3 ) {
							break;
						}
					}
					?>
				</ul>
			</div>

			<div class="col-12 col-sm-4 front-page-widget">
				<h2><?php esc_html_e( '安否情報', 'hametuha' ); ?></h2>
				<ul class="post-list">
					<?php
					foreach (
						get_posts( [
							'post_type'      => 'anpi',
							'posts_per_page' => 3,
						] ) as $post
					) {
						setup_postdata( $post );
						get_template_part( 'parts/loop-front' );
					}
					wp_reset_postdata();
					?>
				</ul>
				<p class="text-center">
					<a href="<?php echo get_post_type_archive_link( 'anpi' ); ?>"
						class="btn btn-outline-primary w-100">
						安否情報トップ
					</a>
				</p>
			</div>
			<div class="col-12 col-sm-4 front-page-widget">
				<h2><?php esc_html_e( '掲示板', 'hametuha' ); ?></h2>

				<ul class="post-list">
					<?php
					foreach (
						get_posts( [
							'post_type'      => 'thread',
							'posts_per_page' => 3,
						] ) as $post
					) {
						setup_postdata( $post );
						get_template_part( 'parts/loop-front' );
					}
					wp_reset_postdata();
					?>
				</ul>
				<p class="text-center">
					<a href="<?php echo get_post_type_archive_link( 'thread' ); ?>"
						class="btn btn-outline-primary w-100">
						掲示板トップ
					</a>
				</p>
			</div>
		</div>
	</div>
	<?php
	$activities_cache = ob_get_flush();
	set_transient( 'hametuha_front_activities', $activities_cache, HOUR_IN_SECONDS );
endif;
?>

	<section class="front-news front-container--inverse">
		<div class="container">
			<h2 class="page-header text-center mb-5" style="border-bottom: none;">
				<small>Literary News</small>
				<br />
				<?php esc_html_e( '文学関連ニュース', 'hametuha' ); ?>
			</h2>
			<div class="row">
				<?php
				foreach (
					get_posts( [
						'post_type'      => 'news',
						'post_status'    => 'publish',
						'posts_per_page' => 4,
					] ) as $post
				) :
					?>
					<div class="col-12 col-sm-3 mb-3 front-news__card">
						<a href="<?php the_permalink( $post ); ?>">
							<div class="card">
								<div class="card-img-top ratio ratio-4x3">
									<?php the_post_thumbnail( 'post-thumbnail' ); ?>
								</div>
								<div class="card-body">
									<h5 class="card-title"><?php the_title(); ?></h5>
									<p class="card-text text-muted mb-0">
										<i class="icon-clock"></i>
										<time
											datetime="<?php the_time( DateTime::ATOM ); ?>"><?php the_time( get_option( 'date_format' ) ); ?></time>
									</p>
									<?php
									$terms = get_the_terms( $post, 'genre' );
									if ( $terms && ! is_wp_error( $terms ) ) {
										?>
										<p class="card-text mb-0">
											<?php foreach ( $terms as $term ) : ?>
												<span class="front-news__term d-inline-block mr-2 mt-2">
													# <?php echo esc_html( $term->name ); ?>
												</span>
											<?php endforeach; ?>
										</p>
										<?php
									}
									?>
								</div>
							</div>
						</a>
					</div>
					<?php
				endforeach;
				wp_reset_postdata();
				?>
			</div>
			<p class="text-center mb-0 mt-3">
				<a href="<?php echo get_post_type_archive_link( 'news' ); ?>"
					class="btn btn-primary">はめにゅートップ</a>
			</p>
		</div>
	</section>

<?php get_footer( 'books' ); ?>

	<section style="padding: 20px 0; background-color: var( --bs-gray-200 );">
		<?php get_footer( 'ebooks' ); ?>
		<p class="text-center">
			<a class="btn btn-primary" href="<?php echo home_url( 'kdp' ); ?>">
				<?php esc_html_e( 'すべての電子書籍', 'hametuha' ); ?>
			</a>
		</p>
	</section>

	<div>
		<div id="about-us" class="container">

			<h2 class="text-center border-bottom pb-3 mb-4">
				破滅派ってなあに？<br />
				<small class="text-muted h6">はじめての方へ</small>
			</h2>

			<div class="row about-us__stats">
				<?php $stats = hametuha_get_site_stats(); ?>
				<div class="col-6 col-sm-3">
					<h3 class="about-us__stats--title">作品数</h3>
					<p class="about-us__stats--score">
						<strong><?php echo number_format( $stats['posts'] ); ?></strong>作品
					</p>
				</div>
				<div class="col-6 col-sm-3">
					<h3 class="about-us__stats--title">作家</h3>
					<p class="about-us__stats--score">
						<strong><?php echo number_format( $stats['authors'] ); ?></strong>
						名
					</p>
				</div>
				<div class="col-6 col-sm-3">
					<h3 class="about-us__stats--title">登録読者</h3>
					<p class="about-us__stats--score">
						<strong><?php echo number_format( $stats['readers'] ); ?></strong>
						名
					</p>
				</div>
				<div class="col-6 col-sm-3">
					<h3 class="about-us__stats--title">継続</h3>
					<p class="about-us__stats--score">
						<?php
						$date  = new DateTime( 'now', wp_timezone() );
						$start = new DateTime( '2007-03-02', wp_timezone() );
						$diff  = $date->diff( $start );
						printf( __( '<strong>%s年</strong>', 'hametuha' ), number_format( $diff->y ) );
						if ( $diff->m ) {
							printf( __( '%dヶ月', 'hametuha' ), $diff->m );
						}
						?>
					</p>
				</div>
			</div>

			<div class="row">

				<div class="col-sm-4 col-12">
					<p class="icon">
						<i class="icon-ha"></i>
					</p>

					<div class="caption">
						<h3>破滅派を知ろう</h3>

						<p>
							破滅派は要するに<strong>オンライン文芸誌</strong>であり、文学作品を発表したり、読んだりできます。<br />
							<a href="<?php echo home_url( '/about/' ); ?>">設立の経緯</a>や<a
								href="<?php echo home_url( '/history/' ); ?>">活動の記録</a>などをご覧頂き、
							恐れを消してください。
						</p>
					</div>
				</div>

				<div class="col-sm-4 col-12">
					<p class="icon">
						<i class="icon-reading"></i>
					</p>

					<div class="caption">
						<h3>破滅派に関わろう</h3>

						<p>
							破滅派はオンライン文芸誌なので、詩や小説といった文学作品を掲載することができます。
							新たな読者との出会いがあなたを待っています。<br />
							「自分は作品を書けないな」という方でも、レビューを残したり、掲示板に書き込んだり、色々な楽しみ方ができます。
						</p>
					</div>
				</div>

				<div class="col-sm-4 col-12">
					<p class="icon">
						<i class="icon-enter"></i>
					</p>

					<div class="caption">
						<h3>まずは新規登録</h3>

						<p>
							なにはともあれ、破滅派にログインしましょう。<br />
							破滅派にアカウントを作成するのに必要なのはメールアドレスだけ。TwitterやFacebookのアカウントでも登録できます。
						</p>
					</div>
				</div>
			</div>

			<p class="text-center mb-5">
				<?php if ( is_user_logged_in() ) : ?>
					<?php if ( current_user_can( 'edit_posts' ) ) : ?>
						<a class="btn btn-lg btn-primary" href="<?php echo admin_url( 'post-new.php' ); ?>">
							作品を書く
						</a>
					<?php else : ?>
						<a class="btn btn-lg btn-primary" href="<?php echo home_url( 'become-author/' ); ?>">
							執筆者になる
						</a>
					<?php endif; ?>
				<?php else : ?>
					<a class="btn btn-lg btn-primary" href="<?php echo wp_login_url(); ?>">
						破滅派にログイン
					</a>
				<?php endif; ?>
			</p>
		</div>

		<?php
		get_template_part( 'parts/share', 'big', [
			'title' => __( '破滅派を広める', 'hametuha' ),
			'desc'  => __( '世界に破滅派を広めて下さい', 'hametuha' ),
		] );
		?>


	</div><!-- front-container -->

<?php
get_footer();
