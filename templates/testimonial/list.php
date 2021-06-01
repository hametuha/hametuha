<?php
/** @var \Hametuha\Rest\Testimonial $this */
/** @var WP_Post $post */
/** @var array $testimonials */
?>
<?php get_header(); ?>

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

		<div class="row row-offcanvas row-offcanvas-right">

			<div class="col-xs-12">

				<h2>
					<?php echo get_the_title( $post ); ?>のレビュー管理
					<small><?php echo number_format( $testimonials['cur_page'] ); ?>
						/ <?php echo number_format( $testimonials['total_page'] ); ?>P</small>
					<a href="<?php echo get_permalink( $post ); ?>" class="btn btn-primary">
						<i class="icon-return"></i> 戻る
					</a>
				</h2>

				<?php
				echo hametuha_format_pagination(
					paginate_links(
						[
							'base'    => home_url( '/testimonials/manage/' . $post->ID . '/%_%', 'https' ),
							'format'  => 'page/%#%/',
							'total'   => $testimonials['total_page'],
							'current' => $testimonials['cur_page'],
						]
					)
				)
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
												<li>
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
												<li>
													<strong
														class="name"><?php echo esc_html( $comment->comment_author ); ?></strong>
												</li>
												<li>
													<strong>公開日</strong>
													<?php echo mysql2date( get_option( 'date_format' ), $comment->comment_date ); ?>
												</li>
												<li>
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
												<li>
													<strong>評価</strong>
													<?php if ( $comment->rank ) : ?>
														<i class="icon-star6"></i> &times; <?php echo $comment->rank; ?>
													<?php else : ?>
														<span class="text-muted">評価なし</span>
													<?php endif; ?>
												</li>
												<li>
													<strong>登録者</strong>
													<?php
													if ( $comment->user_id && ( $user = get_userdata( $comment->user_id ) ) ) {
														echo esc_html( $user->display_name );
													} else {
														echo 'ゲスト';
													}
													?>
												</li>
												<li>
													<strong>優先順位</strong>
													<?php echo number_format( $comment->priority ); ?>
												</li>
											</ul>

										</div>
										<!-- //.testimonialList__meta -->
										<div class="testimonialList__display row">

											<div class="testimonialList__content col-xs-10">
											<?php if ( $comment->twitter ) : ?>
												<?php show_twitter_status( $comment->comment_author_url ); ?>
											<?php else : ?>
												<div class="testimonialList__content--inner">
													<?php echo wpautop( get_comment_meta( $comment->comment_ID, 'comment_excerpt', true ) ?: $comment->comment_content ); ?>
												</div>
											<?php endif; ?>
											</div>

											<div class="testimonialList__controller col-xs-2 clearfix">
												<button data-toggle="modal"
														data-target="#comment-modal-<?php echo $comment->comment_ID; ?>"
														class="testimonialList__link testimonialList__link--edit btn btn-block btn-primary">
													編集
												</button>
												<?php if ( $comment->comment_post_ID == $post->ID ) : ?>
												<a href="<?php echo home_url( '/testimonials/delete/' . $comment->comment_ID . '/', 'https' ); ?>"
												   class="testimonialList__link testimonailList__link--delete btn btn-block btn-danger">削除</a>
												<?php else : ?>
												<button class="btn btn-block btn-danger" disabled>削除</button>
												<?php endif; ?>
											</div>

										</div>

										<div class="modal fade" id="comment-modal-<?php echo $comment->comment_ID; ?>"
											 tabindex="-1" role="dialog">
											<form class="form-horizontal" method="post"
												  action="<?php echo home_url( '/testimonials/edit/' . $comment->comment_ID . '/', 'https' ); ?>">
												<?php wp_nonce_field( 'manage_testimonial' ); ?>
												<div class="modal-dialog" role="document">
													<div class="modal-content">
														<div class="modal-body">
															<?php if ( $post->ID == $comment->comment_post_ID ) : ?>
																<?php if ( ! $comment->twitter ) : ?>
																<div class="form-group">
																	<label for="comment-author"
																		   class="col-sm-4 control-label">名前</label>

																	<div class="col-sm-8">
																		<input type="text" name="comment-author"
																			   id="comment-author"
																			   class="form-control"
																			   value="<?php echo esc_attr( $comment->comment_author ); ?>"/>
																	</div>
																</div>
																<div class="form-group">
																	<label for="comment-rank"
																		   class="col-sm-4 control-label">五段階評価</label>

																	<div class="col-sm-8">
																		<select class="form-control" id="comment-rank"
																				name="comment-rank">
																			<?php
																			foreach (
																				[
																					'0' => '評価なし',
																					'5' => 'とても良い',
																					'4' => '良い',
																					'3' => '普通',
																					'2' => '悪い',
																					'1' => 'とても悪い',
																				] as $index => $label
																			) {
																				printf( '<option value="%1$s"%2$s>%1$s %3$s</option>', $index, selected( $index == $comment->rank, true, false ), $label );
																			}
																			?>
																		</select>
																	</div>
																</div>
																<?php endif; ?>

																<div class="form-group">
																	<label for="comment-url"
																		   class="col-sm-4 control-label">URL</label>

																	<div class="col-sm-8">
																		<input type="text" name="comment-url"
																			   id="comment-url"
																			   class="form-control"
																			   value="<?php echo esc_attr( $comment->comment_author_url ); ?>"/>
																	</div>
																</div>
															<?php endif; ?>

															<div class="form-group">
																<label class="col-sm-4 control-label">公開状態</label>

																<div class="col-sm-8">
																	<label class="radio-inline">
																		<input type="radio" name="comment-status"
																			   value="0" <?php checked( ! $comment->display ); ?>>
																		公開しない
																	</label>
																	<label class="radio-inline">
																		<input type="radio" name="comment-status"
																			   value="1" <?php checked( $comment->display ); ?>>
																		公開する
																	</label>
																</div>
															</div>

															<div class="form-group">
																<label class="col-sm-4 control-label"
																	   for="comment-priority">
																	優先順位
																</label>

																<div class="col-sm-8">
																	<input type="number" name="comment-priority"
																		   id="comment-priority"
																		   value="<?php echo esc_attr( $comment->priority ); ?>"
																		   min="0">
																	<?php help_tip( 'コメントは「優先順位の高さ＞日付の新しい順」で表示されます。重要なものの順位を高くしてください。' ); ?>
																</div>
															</div>

															<?php if ( ! $comment->twitter ) : ?>

																<div class="form-group">
																<label class="col-sm-4 control-label"
																	   for="comment-content">コメント本文</label>

																<div class="col-sm-8">
																	<?php if ( 'review' === $comment->comment_type ) : ?>
																		<textarea class="form-control"
																				  id="comment-content"
																				  name="comment-content"
																				  rows="5"><?php echo esc_textarea( $comment->comment_content ); ?></textarea>
																	<?php else : ?>
																		<textarea class="form-control"
																			id="comment-content"
																			name="comment-excerpt"
																			rows="3"><?php echo esc_textarea( get_comment_meta( $comment->comment_ID, 'comment_excerpt', true ) ); ?></textarea>
																		<span class="help-block">
																			投稿へ付けられたコメントの一部を抜粋できます。含まれていない文字列は無効です。
																			抜粋がない場合は全文が表示されます。
																		</span>
																		<pre><?php echo esc_html( $comment->comment_content ); ?></pre>
																	<?php endif; ?>
																</div>
															</div>

															<?php endif; ?>

														</div>
														<!-- //.modal-body -->

														<div class="modal-footer">
															<button type="button" class="btn btn-default"
																	data-dismiss="modal">キャンセル
															</button>
															<input type="submit" class="btn btn-primary" value="更新">
														</div>

													</div>
													<!-- //.modal-content -->
												</div>
												<!-- //.modal-dialog -->
											</form>
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
