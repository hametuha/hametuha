<?php
$series = Hametuha\Model\Series::get_instance();
get_header(); ?>

<?php
if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		?>

	<article id="viewing-content" <?php post_class(); ?> itemscope itemtype="http://schema.org/BlogPosting" itemprop="mainEntityOfPage">
		<span class="hidden" itemprop="url"><?php echo the_permalink(); ?></span>
		<span class="hidden" itemprop="publisher">破滅派</span>

		<div id="content-wrapper">
			<?php if ( has_post_thumbnail() ) : ?>
				<div class="single-post-thumbnail text-center">
					<?php the_post_thumbnail( 'large', array( 'item-prop' => 'image' ) ); ?>
				</div>
			<?php endif; ?>
			<div class="work-wrapper container">

				<div class="work-meta row">

					<div class="inner">

						<h1 itemprop="headline"><?php the_title(); ?></h1>

						<?php the_series( '<p class="series">', sprintf( '（第%s話）</p>', $series->get_index() ) ); ?>

						<?php
							$list = [];
							foreach ( [
								'campaign' => esc_html( '%s応募作品', 'hametuha' ),
								\Hametuha\Hooks\Qualification::TAXONOMY => esc_html( '%s', 'hametuha' ),
							] as $taxonomy => $replace ) {
								$terms = get_the_terms( get_the_ID(), $taxonomy );
								if ( ! $terms || is_wp_error( $terms ) ) {
									continue;
								}
								foreach ( $terms as $term ) {
									$list[] = sprintf( $replace, sprintf( '<a href="%s">%s</a>', esc_url( get_term_link( $term ) ), esc_html( $term->name ) ) );
								}
							}
							if ( ! empty( $list ) ) {
								printf( '<p class="campaign">%s</p>', implode( esc_html__( '、', 'hametuha' ), $list ) );
							}
						?>

						<p class="author">
							<a href="#post-author"><?php the_author(); ?></a>
						</p>

						<p class="genre">
							<?php
							echo implode( ' ', array_map( function ( $cat ) {
								return sprintf( '<a href="%s" itemprop="genre">%s</a>', get_category_link( $cat ), esc_html( $cat->name ) );
							}, get_the_category() ) )
							?>
						</p>

						<p class="length">
								<?php the_post_length( '<span itemprop="wordCount">', '</span>', '-' ); ?>文字
						</p>

							<?php if ( has_excerpt() ) : ?>
							<div class="desc" itemprop="description">
								<?php the_excerpt(); ?>
							</div>
						<?php endif; ?>
					</div>

				</div>
				<!-- //.post-meta-single -->

					<?php
					$should_hide = $series->should_hide();
					if ( $should_hide ) {
						if ( ! current_user_can( 'edit_post', get_the_ID() ) ) {
							add_filter( 'the_content', 'hametuha_series_hide', 100 );
						} else {
							echo <<<HTML
					<div class="alert alert-info">
						<p>
			                この投稿は販売中のため閲覧制限がかかっていますが、あなたには編集権限があるので表示しています。
						</p>
					</div>
HTML;
						}
					}
					?>

				<div class="work-content row" itemprop="articleBody">

					<?php the_content(); ?>

					<?php
					if ( ! $should_hide ) {
						hametuha_footer_notes();
					}
					?>

					<?php if ( is_last_page() ) : ?>
						<p id="work-end-ranker" class="text-center" data-post="<?php the_ID(); ?>"><i
								class="icon-ha"></i></p>
					<?php endif; ?>

					<?php
					wp_link_pages( array(
						'before'      => '<p class="link-pages">ページ: ',
						'after'       => '</p>',
						'link_before' => '<span>',
						'link_after'  => '</span>',
					) );
					?>

					<?php if ( is_user_logged_in() && ! is_preview() ) : ?>
						<text-holder selection="selection" selection-top="selectionTop" content-height="contentHeight" id="<?php echo the_ID(); ?>"></text-holder>
					<?php endif; ?>

				</div>
				<!-- //.single-post-content -->

					<?php
					if ( $external = hametuha_external_url() ) :
						if ( hametuha_external_url_is_active() ) {
							$limit_message = sprintf( __( 'この作品は%sまで破滅派で読むことができます。', 'hametuha' ), hametuha_external_url_limit( get_option( 'date_format' ) ) );
						} else {
							$limit_message = __( 'この作品の続きは外部にて読むことができます。', 'hametuha' );
						}
						?>
					<div class="alert alert-info text-center">
						<?php echo esc_html( $limit_message ); ?>
					</div>
						<?php if ( $ogp = hametuha_remote_ogp( $external ) ) : ?>
						<div class="external-link">
							<div class="row">
								<?php if ( $ogp['img'] ) : ?>
								<div class="col-xs-12 col-sm-3">
									<img loading="lazy" src="<?php echo esc_url( $ogp['img'] ); ?>" class="img-responsive" alt="<?php echo esc_attr( $ogp['title'] ); ?>"/>
								</div>
								<?php endif; ?>
								<div class="col-xs-12 col-sm-9">
									<h3><?php echo esc_html( $ogp['title'] ); ?></h3>
									<p class="text-muted"><?php echo esc_html( $ogp['desc'] ); ?></p>
									<a class="btn btn-primary" href="<?php echo esc_url( $external ); ?>" rel="nofollow" target="_blank">外部サイトへ移動</a>
								</div>
							</div>
						</div>
					<?php else : ?>
						<div class="alert alert-danger text-center">
							情報の取得に失敗しました（<a href="<?php echo esc_url( $external ); ?>" target="_blank" class="alert-link" rel="nofollow">外部サイトへ移動する</a>）
						</div>
					<?php endif; ?>
					<?php endif; ?>

				<p class="text-center pub-date">
					<span><?php the_time( 'Y年n月j日' ); ?></span>公開
					<span class="hidden" itemprop="datePublished"><?php the_time( 'c' ); ?></span>
					<span class="hidden" itemprop="dateModified"><?php the_modified_date( 'c' ); ?></span>
					<?php if ( $corrected = hametuha_first_corrected( true ) ) : ?>
						（初出 <?php echo $corrected; ?>）
					<?php endif; ?>
				</p>

					<?php if ( is_series() ) : ?>
					<p class="series-pager-title text-center">
						作品集『<?php the_series(); ?>』<?php echo $series->index_label(); ?>
						（全<?php echo $series->get_total( $post->post_parent ); ?>話）
					</p>
						<?php get_template_part( 'parts/alert', 'kdp' ); ?>
					<ul class="series-pager">
						<?php echo $series->prev( '<li class="previous">' ); ?>
						<?php echo $series->next( '<li class="next">' ); ?>
					</ul>
				<?php endif; ?>


				<div id="single-post-footernote" class="row">
					&copy; <span itemprop="copyrightYear"><?php the_time( 'Y' ); ?></span> <?php the_author(); ?>
				</div>

				<p class="finish-nav">
					<?php if ( ( $campaigns = get_the_terms( get_post(), 'campaign' ) ) && ! is_wp_error( $campaigns ) ) : ?>
						これは<?php the_terms( get_the_ID(), 'campaign' ); ?>の応募作品です。<br />
						他の作品ともどもレビューお願いします。<br />
					<?php else : ?>
						読み終えたらレビューしてください<br/>
					<?php endif; ?>
					<i class="icon-point-down"></i>
				</p>

				<?php
				// 関連タグを取得
				$terms = hametuha_terms_to_hashtag( [ 'nouns', 'post_tag', 'campaign', 'category' ], get_post(), true );
				if ( $terms ) :
					?>
					<div>
						<h3 class="text-center">この作品のタグ</h3>
						<p class="post-tags">
							<?php echo implode( ' ', $terms ); ?>
						</p>
					</div>
				<?php endif; ?>

				<?php get_header( 'breadcrumb' ); ?>

			</div><!-- //.work-wrapper -->

				<?php get_template_part( 'parts/share', 'big' ); ?>

			<section class="single-author-section" id="post-author">

				<div class="container">
					<h2 class="list-title list-title-inverse">
						<?php esc_html_e( '著者', 'hametuha' ); ?>
					</h2>

					<div class="author-container m20">
						<?php get_template_part( 'parts/author' ); ?>
					</div>

					<?php get_template_part( 'parts/list', 'author' ); ?>

				</div>

			</section>

			<?php get_sidebar( 'related' ); ?>

			<div class="container">

				<?php get_sidebar( 'books', [
					'title' => true,
				] ); ?>

				<h2 class="series__title--share text-center">
					<small class="series__title--caption">eBooks</small>
					<?php esc_html_e( '破滅派の電子書籍', 'hametuha' ); ?>
				</h2>

				<?php
				hameplate( 'templates/recommendations', '', [
					'excludes' => wp_get_post_parent_id() ?: 0,
					'author'   => get_the_author_meta( 'ID' ),
					'fill'     => true,
				] )
				?>
			</div>
			<!-- // .work-wrapper -->


		</div>
		<!-- //#content-wrapper -->


		<div id="finish-wrapper" class="overlay-container">
			<div class="container">

				<h3>リストに追加する</h3>

				<p class="text-muted">
					リスト機能とは、気になる作品をまとめておける機能です。公開と非公開が選べますので、
					短編集として公開したり、お気に入りのリストとしてこっそり楽しむこともできます。
				</p>

				<hr/>

				<?php if ( is_user_logged_in() ) : ?>

					<form class="list-save-manager" method="post"
						  action="<?php echo esc_url( Hametuha\Rest\ListCreator::save_link( get_the_ID() ) ); ?>">
						<?php wp_nonce_field( 'list-save' ); ?>
						<div id="list-changer">
							<?php
							$lists           = new WP_Query( [
								'my-content'     => 'lists',
								'post_type'      => 'lists',
								'post_author'    => 0,
								'post_status'    => [ 'publish', 'private' ],
								'orderby'        => 'post_title',
								'order'          => 'DESC',
								'posts_per_page' => - 1,
							] );
							$current_post_id = get_the_ID();
							if ( $lists->have_posts() ) {
								$html = <<<HTML
								<div class="checkbox">
									<label>
					                    <input type="checkbox" name="lists[]" value="%d"%s>
					                    %s
									</label>
								</div>
HTML;
								while ( $lists->have_posts() ) {
									$lists->the_post();
									printf( $html, get_the_ID(),
										checked( in_lists( $current_post_id, get_the_ID() ), true, false ),
										esc_html( ( $post->post_status == 'publish' ? '公開　: ' : '' ) . get_the_title() )
									);
								}
								wp_reset_postdata();
							}
							?>

						</div>

						<p class="text-muted">リストを選んで保存ください。<strong><?php the_title(); ?></strong>がリストに追加されます。リストは新たに作成することもできます。
						</p>

						<div class="row">

							<div class="col-xs-6 text-left">
								<input type="submit" class="btn btn-primary" value="変更を保存"/>
							</div>

							<div class="col-xs-6 text-right">
								<a class="btn btn-success list-creator" title="リストを作成する"
								   href="<?php echo esc_url( Hametuha\Rest\ListCreator::form_link() ); ?>"><i
										class="icon-plus-circle"></i> リストを作成</a>
							</div>

						</div>

					</form>

				<?php else : ?>

					<p class="alert alert-warning">
						リスト機能を利用するには<a class="alert-link" href="<?php echo wp_login_url( get_permalink() ); ?>">ログイン</a>する必要があります。
					</p>

				<?php endif; ?>


			</div>
		</div>


		<div id="reviews-wrapper" class="overlay-container">
			<div class="container">
				<div>
					<?php Hametuha\Ajax\Feedback::form( 'parts/feedback', 'you', [ 'id' => 'review-form' ] ); ?>
				</div>

				<hr/>

				<?php Hametuha\Ajax\Feedback::all_review( get_the_ID() ); ?>

			</div>
		</div>

		<div id="comments-wrapper" class="overlay-container">
			<div id="post-comment" class="container">
				<?php comments_template(); ?>
			</div>
		</div>


		<a class="overlay-close reset-viewer" href="#">
			<i class="icon-esc"></i> 作品に戻る
		</a>

	</article>

		<?php
endwhile;
endif;
?>


	<footer id="footer-single">
		<nav class="container">
			<ul class="clearfix">
				<li>
					<a href="#finish-wrapper">
						<i class="icon-books"></i><br/>
						<span>リスト</span>
					</a>
				</li>
				<li class="finished-container">
					<a href="#reviews-wrapper">
						<i class="icon-star6"></i><br/>
						<span>レビュー</span>
					</a>
				</li>
				<li>
					<a href="#comments-wrapper">
						<i class="icon-bubbles"></i><br/>
						<span>コメント</span>
						<?php if ( $count = get_comments_number() ) : ?>
							<small class="comment-count badge">
								<?php echo $count > 100 ? '99+' : $count; ?>
							</small>
						<?php endif; ?>
					</a>
				</li>
			</ul>
		</nav>
		<!-- //.container -->
	</footer>

<?php
get_footer();
