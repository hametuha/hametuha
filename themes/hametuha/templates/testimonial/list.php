<?php
/**
 * レビューの管理
 *
 * @feature-group series
 * @var \Hametuha\Rest\Testimonial $this
 *
 * @var WP_Post $post
 * @var array $testimonials
 */

wp_enqueue_script( 'hametuha-components-edit-testimonials-helper' );
get_header();
?>

	<div id="breadcrumb" itemprop="breadcrumb">
		<div class="container">
			<i class="icon-location5"></i>
			<a href="<?php echo home_url( '' ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
			&gt;
			<a href="<?php echo get_permalink( $post->ID ); ?>"><?php echo get_the_title( $post ); ?></a>
			&gt; レビュー管理
		</div>
	</div>


	<div class="container single">

		<div class="row">

			<div class="col-12">

				<h2>
					<?php echo get_the_title( $post ); ?>のレビュー管理
					<small><?php echo number_format( $testimonials['cur_page'] ); ?>
						/ <?php echo number_format( $testimonials['total_page'] ); ?>P</small>
					<a href="<?php echo get_permalink( $post ); ?>" class="btn btn-primary">
						<i class="icon-return"></i> 戻る
					</a>
				</h2>

				<?php
				echo hametuha_format_pagination(paginate_links([
					'base'    => home_url( '/testimonials/manage/' . $post->ID . '/%_%', 'https' ),
					'format'  => 'page/%#%/',
					'total'   => $testimonials['total_page'],
					'current' => $testimonials['cur_page'],
				]))
				?>


				<div class="alert alert-info">
					このページで<strong>表示する</strong>となっているレビューが
					<a href="<?php echo get_permalink( $post ); ?>" class="alert-link">作品集ページ</a>に表示されます。作品の魅力を伝えるレビューをたくさん表示しましょう。
				</div>

				<div>

					<div class="statistics statistics--testimonials">

						<?php if ( $testimonials['rows'] ) : ?>
							<ol class="testimonialList">

								<?php foreach ( $testimonials['rows'] as $comment ) : ?>

									<li class="testimonialList__item">

										<div class="testimonialList__meta">
											<ul class="list-inline">
												<li class="list-inline-item">
													<?php if ( $comment->display ) : ?>
														<span class="ok">
															<i class="icon-bubble-check"></i>
															表示する
														</span>
													<?php else : ?>
														<span class="ng">
															<i class="icon-bubble-cancel"></i>
															表示しない
														</span>
													<?php endif; ?>
												</li>
												<li class="list-inline-item">
													<strong
														class="name"><?php echo esc_html( $comment->comment_author ); ?></strong>
												</li>
												<li class="list-inline-item">
													<strong>公開日</strong>
													<?php echo mysql2date( get_option( 'date_format' ), $comment->comment_date ); ?>
												</li>
												<li class="list-inline-item">
													<strong>種別</strong>
													<?php
													if ( ! $comment->comment_type ) {
														echo '投稿へのコメント：';
														printf( '<a href="%s">%s</a>', get_permalink( $comment->comment_post_ID ), get_the_title( $comment->comment_post_ID ) );
													} elseif ( $comment->twitter ) {
														echo 'twitter';
													} elseif ( $comment->amazon ) {
														echo 'amazonレビュー';
													} else {
														echo 'その他';
													}
													?>
												</li>
												<li class="list-inline-item">
													<strong>評価</strong>
													<?php if ( $comment->rank ) : ?>
														<i class="icon-star6"></i> &times; <?php echo $comment->rank; ?>
													<?php else : ?>
														<span class="text-muted">評価なし</span>
													<?php endif; ?>
												</li>
												<li class="list-inline-item">
													<strong>登録者</strong>
													<?php
													if ( $comment->user_id && ( $user = get_userdata( $comment->user_id ) ) ) {
														echo esc_html( $user->display_name );
													} else {
														echo 'ゲスト';
													}
													?>
												</li>
												<li class="list-inline-item">
													<strong>優先順位</strong>
													<?php echo number_format( $comment->priority ); ?>
												</li>
											</ul>

										</div>
										<!-- //.testimonialList__meta -->
										<div class="testimonialList__display row">

											<div class="testimonialList__content col-10">
											<?php if ( $comment->twitter ) : ?>
												<?php show_twitter_status( $comment->comment_author_url ); ?>
											<?php else : ?>
												<div class="testimonialList__content--inner">
													<?php echo wpautop( get_comment_meta( $comment->comment_ID, 'comment_excerpt', true ) ?: $comment->comment_content ); ?>
												</div>
											<?php endif; ?>
											</div>

											<div class="testimonialList__controller col-2 clearfix">
												<button data-bs-toggle="modal"
														data-bs-target="#comment-modal-<?php echo $comment->comment_ID; ?>"
														class="testimonialList__link testimonialList__link--edit btn w-100 btn-primary mb-2 mt-2">
													編集
												</button>
												<?php if ( $comment->comment_post_ID == $post->ID ) : ?>
													<button data-path="<?php echo esc_attr('/hametuha/v1/testimonials/' . $comment->comment_ID . '/' ); ?>"
														class="testimonial-delete btn w-100 btn-danger">
														削除
													</button>
												<?php else : ?>
													<button class="btn w-100 btn-danger" disabled>削除</button>
												<?php endif; ?>
											</div>

										</div>

										<div class="modal fade" id="comment-modal-<?php echo $comment->comment_ID; ?>"
											 tabindex="-1" role="dialog">
											<div class="modal-dialog" role="document">
												<div class="modal-content">
													<div class="modal-body">
														<?php get_template_part( 'templates/testimonial/form-add', '', [
															'comment' => $comment,
															'layout'  => 'horizontal',
														] ) ?>
													</div>
													<!-- //.modal-body -->
												</div>
												<!-- //.modal-content -->
											</div>
											<!-- //.modal-dialog -->
										</div>
										<!-- //.modal -->

									</li>

								<?php endforeach; ?>
							</ol>
						<?php else : ?>
							<div class="alert alert-warning">
								該当するレビューはありませんでした。
							</div>
						<?php endif; ?>

					</div>


				</div>

			</div>

		</div>
		<!-- //.row-offcanvas -->
	</div><!-- //.container -->

<?php get_footer(); ?>
