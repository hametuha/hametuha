<?php

$ideas = \Hametuha\Model\Ideas::get_instance();

get_header();
get_header( 'sub' );
if ( current_user_can( 'read' ) ) {
	wp_enqueue_script( 'hametuha-components-ideas-stock' );
}
get_template_part( 'templates/idea/form' );
?>

<?php get_header( 'breadcrumb' ); ?>

	<div class="container">

		<div class="row ideas__row">

			<?php
			if ( have_posts() ) :
				while ( have_posts() ) :
					the_post();
					?>

					<article itemscope
						itemtype="http://schema.org/BlogPosting" <?php post_class( 'col-xs-12 main-container' ); ?>>

						<!-- title -->
						<h1 class="ideas__title mt-7" itemprop="headline">
							「<?php the_title(); ?>」
							<small class="d-inline-block">
								<?php
								echo ( get_current_user_id() == $post->post_author ) ? esc_html__( 'あなたのアイデア', 'hametuha' ) : esc_html__( 'アイデア', 'hametuha' );
								// ステータス
								$class_name = in_array( get_post_status(), [
									'private',
									'protected'
								] ) ? 'danger' : 'success';
								?>
								<span
									class="badge rounded-pill text-bg-<?php echo $class_name; ?>"><?php echo esc_html( get_post_status_object( get_post_status() )->label ); ?></span>
							</small>
						</h1>

						<?php
						$terms = get_the_tags();
						if ( $terms && ! is_wp_error( $terms ) ) :
							?>
							<p class="mt-3">
								<?php
								echo implode( ' ', array_map( function ( $term ) {
									return sprintf(
										'<a class="term-link" href="%s">#%s</a>',
										esc_url( add_query_arg( [
											'tag' => rawurlencode( $term->slug ),
										], get_post_type_archive_link( 'ideas' ) ) ),
										esc_html( $term->name )
									);
								}, $terms ) );
								?>
							</p>
						<?php endif; ?>

						<!-- Meta data -->
						<div <?php post_class( 'ideas__meta mt-3' ); ?>>

							<ul class="ideas__list">

								<li class="ideas__item ideas__item--author">
									<?php
									// 画像
									$author = get_userdata( get_the_author_meta( 'ID' ) );
									echo get_avatar( $post->post_author, 32, '', $author->display_name, [ 'class' => 'idea__avatar img-circle avatar' ] );
									// 名前
									$author_name = esc_html( get_the_author_meta( 'display_name' ) );
									if ( user_can( $post->post_author, 'edit_posts' ) ) :
										$author_url = esc_url( home_url( '/doujin/detail/' . get_the_author_meta( 'user_nicename' ) . '/' ) );
										?>
										<a itemprop="author"
											href="<?php echo $author_url; ?>"><?php echo $author_name; ?></a>
									<?php else : ?>
										<span itemprop="author">
										<?php echo $author_name; ?>
									</span>
									<?php endif; ?>
								</li>

								<?php
								// 元ネタがある場合
								if ( $post->idea_author ) :
									?>
									<li class="ideas__item ideas__item--source">
										<i class="icon-user"></i>
										<?php
										printf( esc_html__( 'ネタ元: ', 'hametuha' ), esc_html( $post->idea_author ) );
										?>
										<?php echo esc_html( $post->idea_author ); ?>
									</li>
								<?php endif; ?>

								<?php if ( $post->idea_source ) : ?>
									<li class="ideas__item ideas__item--source">
										<i class="icon-link"></i> <?php linkify( $post->idea_source ); ?> より
									</li>
								<?php endif; ?>

								<li class="ideas__item ideas__item--comment">
									<i class="icon-bubbles2"></i>
									<?php comments_number( 'なし', '1件', '%件' ); ?>
								</li>
							</ul>
						</div><!-- //.post-meta -->

						<div class="ideas__body" itemprop="articleBody">
							<?php echo wpautop( WPametu::helper()->str->auto_link( strip_tags( get_the_content() ) ) ); ?>
							<?php if ( ! is_user_logged_in() ) : ?>
								<div class="ideas__body--hide">
									<div class="alert alert-danger text-center">
										破滅派に投稿されたアイデアを見るためには、<br />
										<a class="alert-link"
											href="<?php echo wp_login_url( get_permalink() ); ?>">ログイン</a>
										が必要です。<br />
										ケチですいません……
									</div>
								</div>
							<?php endif ?>
						</div><!-- //.post-content -->

						<hr class="mb-5 mt-5" />

						<div class="ideas__actions">
							<h2 class="ideas__title--meta text-center mb-5">
								<small class="text-muted">Actions</small><br />
								このアイデアを……
							</h2>
							<div class="row">
								<?php if ( current_user_can( 'read' ) ) : ?>
									<div class="col-sm-4 col-xs-12">
										<?php if ( get_current_user_id() == get_the_author_meta( 'ID' ) ) : ?>
											<button class="btn btn-primary btn-block" data-post-id="<?php the_ID(); ?>"">
												編集する
											</button>
										<?php else :
											// ストックボタンを表示する
											$class_names = [ 'stock-container' ];
											if ( $ideas->is_stocked( get_current_user_id(), get_the_ID() ) ) :
												?>
												<button class="btn btn-primary btn-block" data-stock="<?php the_ID() ?>" data-stock-action="delete">
													ストック済み
												</button>
											<?php else : ?>
												<button class="btn btn-primary btn-block" data-stock="<?php the_ID(); ?>" data-stock-action="post">
													ストックする
												</button>
											<?php endif; ?>
										<?php endif; ?>
									</div>

									<div class="col-sm-4 col-xs-12">
										<button class="btn btn-info btn-block" type="button" data-bs-toggle="collapse"
											data-bs-target="#ideaRecommendForm" aria-expanded="false" aria-controls="ideaRecommendForm">
											他の人に薦める
										</button>
									</div>

									<div class="col-sm-4 col-xs-12">
										<a class="btn btn-success btn-block" rel="nofollow"
											href="<?php echo home_url( '/my/ideas/new/' ); ?>" data-action="post-idea">もっといいアイデア</a>
									</div>

								<?php else : ?>
									<div class="col-xs-12">
										<div class="alert-info">
											<a href="<?php echo wp_login_url( get_permalink() ); ?>" rel="nofollow"
											class="btn btn-block btn-primary">
												ログイン
											</a>
											するとアイデアをストックしたり勧めたりできるようになります。
										</div>
									</div>
								<?php endif; ?>
							</div>
						</div>
						<div class="ideas__statistic">
							<?php
							$total = $post->stock;
							if ( $total ) :
								?>
								<p class="text-center text-muted"><?php echo number_format( $total ); ?>
									人がストックしています。</p>
								<ul class="ideas__stockers text-center">
									<?php
									$int = 0;
									foreach ( $ideas->get_stockers( get_the_ID() ) as $user ) :
										?>
										<li class="ideas__stocker">
											<?php if ( $user->ID == get_current_user_id() || ! user_can( $user->ID, 'edit_posts ' )) : ?>
												<span class="ideas__stocker--link">
													<?php
													echo get_avatar( $user->ID, 32 );
													echo ( $user->ID == get_current_user_id() ) ? 'あなた' : esc_html( $user->display_name );
													?>
												</span>
											<?php else : ?>
												<a class="ideas__stocker--link"
													href="<?php echo home_url( '/doujin/detail/' . $user->user_nicename . '/' ); ?>">
													<?php
													echo get_avatar( $user->ID, 32 );
													echo esc_html( $user->display_name );
													?>
												</a>
											<?php endif; ?>
										</li>
										<?php
										$int ++;
									endforeach;
									?>
									<?php if ( $total > 10 ) : ?>
										<li class="ideas__stocker ideas__stocker--more text-muted">
											ほか<?php echo number_format( $total - 10 ); ?>人
										</li>
									<?php endif; ?>
								</ul>
							<?php else : ?>
								<p class="text-center text-warning">
									まだ誰もストックしていません！ <strong>早いもの勝ち！</strong>
								</p>
							<?php endif; ?>
						</div><!-- //.ideas_statistic -->

						<?php if ( current_user_can( 'read' ) ) : ?>

							<?php
							hameplate('templates/idea/form', 'recommend', [
								'idea' => get_post(),
							] )
							?>
						<?php endif; ?>

						<hr class="mb-5 mt-5" />

						<div class="more">
							<?php comments_template(); ?>
						</div>

						<?php get_template_part( 'parts/share' ); ?>


					</article><!-- //.single-container -->

				<?php
				endwhile;
			endif;
			?>

			<div class="col-xs-12">

				<h2 class="dividing-header">
					<?php printf( esc_html__( '%sの他のアイデア', 'hametuha' ), get_the_author_meta( 'display_name' ) ); ?>
				</h2>

				<?php
				$ideas = hametuha_get_author_work_siblings( 6, get_queried_object() );
				if ( $ideas ) {
					?>
					<div class="card-list row">
						<?php
						foreach ( $ideas as $post ) {
							setup_postdata( $post );
							get_template_part( 'parts/loop', 'ideas' );
						}
						wp_reset_postdata();
						?>
					</div>
					<?php
				} else {
					printf(
						'<p class="text-muted mb-5">%s</p>',
						esc_html__( 'この投稿者には他にアイデアがないようです……', 'hametuha' )
					);
				}
				?>

				<div class="d-flex justify-content-center mt-3 mb-5">
					<a class="btn btn-outline-primary"
						href="<?php echo esc_url( get_post_type_archive_link( 'ideas' ) ); ?>">
						<?php esc_html_e( 'アイデア帳トップへ', 'hametuha' ); ?>
					</a>
				</div>

				<?php
				// タグクラウドを出力
				get_template_part( 'templates/idea/tag-cloud' );
				?>
			</div>

		</div><!-- //.row-offcanvas -->
	</div><!-- //.container -->

<?php
get_footer( 'books' );
get_footer();
