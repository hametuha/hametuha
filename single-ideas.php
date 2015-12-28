<?php

$ideas = \Hametuha\Model\Ideas::get_instance();

get_header();
?>

<?php get_header( 'breadcrumb' ) ?>

	<div class="container single">

		<div class="row ideas__row">

			<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

				<article itemscope
				         itemtype="http://schema.org/BlogPosting" <?php post_class( 'col-xs-12 col-sm-9 main-container' ) ?>>

					<?php get_template_part( 'parts/jumbotron', 'ideas' ) ?>


					<!-- title -->
					<div class="page-header">

						<h1 class="ideas__title" itemprop="headline">
							<small>アイデア名：</small> <?php the_title(); ?>
						</h1>

					</div><!-- //.page-header -->


					<!-- Meta data -->
					<div <?php post_class( 'ideas__meta' ) ?>>

						<ul class="ideas__list">

							<li class="ideas__item ideas__item--author">
								<?= get_avatar( $post->post_author, 32 ); ?>
								<?php
								$author_name = esc_html( $post->idea_author ?: get_the_author_meta( 'display_name' ) );
								$author_url = esc_url( home_url( '/doujin/detail/' . get_the_author_meta( 'user_nicename' ) . '/' ) );
								if ( ! $post->idea_author && user_can( $post->post_author, 'edit_posts' ) ) :
									?>
									<a itemprop="author" href="<?= $author_url ?>"><?= $author_name ?></a>
								<?php else : ?>
									<span itemprop="author">
										<?= $author_name ?>
									</span>
								<?php endif; ?>
							</li>

							<?php if ( $post->idea_source ) : ?>
								<li class="ideas__item ideas__item--source">
									<i class="icon-link"></i> <?php linkify( $post->idea_source ) ?> より
								</li>
							<?php endif; ?>

							<li class="ideas__item ideas__item--genre">
								<i class="icon-tags"></i> <?= implode( ', ', array_map( function ( $tag ) {
									return sprintf( '<a href="%s">%s</a>', get_tag_link( $tag ) . '?post_type=ideas', esc_html( $tag->name ) );
								}, get_the_tags( get_the_ID() ) ) ) ?>
							</li>

							<li class="ideas__item ideas__item--comment">
								<i class="icon-bubbles2"></i>
								<?php comments_number( 'なし', '1件', '%件' ) ?>
							</li>

							<?php if ( current_user_can( 'edit_post', get_the_ID() ) ) : ?>
								<li class="ideas__item ideas__item--edit">
									<a class="btn btn-xs btn-primary" href="<?= home_url( "/my/ideas/edit/{$post->ID}/" ) ?>" data-action="edit-idea">
										編集
									</a>
								</li>
								<li class="ideas__item ideas__item--edit">
									<a class="btn btn-xs btn-danger" href="#" data-action="delete-idea" data-post-id="<?php the_ID() ?>">
										削除
									</a>
								</li>
							<?php endif; ?>

							<li class="ideas__item ideas__item--status">
								<?php $class_name = in_array(get_post_status(), ['private', 'protected']) ? 'default' : 'success'; ?>
								<span class="label label-<?= $class_name ?>"><?= esc_html(get_post_status_object(get_post_status())->label) ?></span>
							</li>
						</ul>
					</div><!-- //.post-meta -->

					<?php if ( get_current_user_id() == $post->post_author ) : ?>
					<div class="alert alert-info ideas_owning">
						これはあなたのアイデアです。
					</div>
					<?php endif; ?>

					<div class="ideas__body" itemprop="articleBody">
						<?= wpautop( WPametu::helper()->str->auto_link( strip_tags( get_the_content() ) ) ) ?>
						<?php if ( ! is_user_logged_in() ) : ?>
							<div class="ideas__body--hide">
								<div class="alert alert-danger text-center">
									破滅派に投稿されたアイデアを見るためには、<br />
									<a class="alert-link" href="<?= wp_login_url( get_permalink() ) ?>">ログイン</a>
									が必要です。<br />
									ケチですいません……
								</div>
							</div>
						<?php endif ?>
					</div><!-- //.post-content -->

					<hr />

					<div class="ideas__actions">
						<h3 class="ideas__title--meta text-center">
							<small>Actions</small><br />
							このアイデアを……
						</h3>
						<div class="row">
						<?php if ( current_user_can( 'read' ) ) : ?>

							<div class="col-sm-4 col-xs-12">
								<?php if ( current_user_can( 'edit_posts' ) ) : ?>
									<?php if ( get_current_user_id() == get_the_author_meta( 'ID' ) ) : ?>
										<a class="btn btn-primary btn-block" rel="nofollow" href="#" disabled>あなたのアイデア</a>
									<?php elseif ( $ideas->is_stocked( get_current_user_id(), get_the_ID() ) ) : ?>
										<a class="btn btn-primary btn-block" rel="nofollow" href="<?php the_permalink() ?>" disabled>ストック済み</a>
									<?php else : ?>
										<a class="btn btn-primary btn-block" rel="nofollow" href="<?php the_permalink() ?>" data-stock="<?php the_ID(); ?>">ストックする</a>
									<?php endif; ?>
								<?php else : ?>
									<a class="btn btn-primary btn-block" rel="nofollow" href="#" disabled>ストック</a>
								<?php endif; ?>
							</div>

							<div class="col-sm-4 col-xs-12">
								<a class="btn btn-info btn-block" rel="nofollow" href="<?= home_url('/my/ideas/recommend/'.get_the_ID().'/') ?>" data-recommend="<?php the_ID(); ?>">他の人に薦める</a>
							</div>

							<div class="col-sm-4 col-xs-12">
								<a class="btn btn-success btn-block" rel="nofollow" href="<?= home_url( '/my/ideas/new/' ) ?>" data-action="post-idea">もっといいアイデア</a>
							</div>

						<?php else : ?>
							<div class="col-xs-12">
								<a href="<?= wp_login_url( get_permalink() ) ?>" class="btn btn-block btn-primary">ログインしてストックしたり勧めたりする</a>
							</div>
						<?php endif; ?>
						</div>
					</div>
					<div class="ideas__statistic">
						<?php
						$total = $post->stock;
						if ( $total ) :
							?>
							<p class="text-center text-muted"><?= number_format( $total ) ?>人がストックしています。</p>
							<ul class="ideas__stockers text-center">
								<?php $int = 0;
								foreach ( $ideas->get_stockers( get_the_ID() ) as $user ) : ?>
									<li class="ideas__stocker">
										<a class="ideas__scoker--link"
										   href="<?= home_url( '/doujin/detail/' . $user->user_nicename . '/' ) ?>">
											<?= get_avatar( $user->ID, 32 ) ?>
											<?= esc_html( $user->ID == get_current_user_id() ? 'あなた' : $user->display_name ) ?>

										</a>
									</li>
									<?php $int ++; endforeach; ?>
								<?php if ( $total > 10 ) : ?>
									<li class="ideas__stocker ideas__stocker--more text-muted">
										ほか<?= number_format( $total - 10 ) ?>人
									</li>
								<?php endif; ?>
							</ul>
						<?php else : ?>
							<p class="text-center text-warning">
								まだ誰もストックしていません！ <strong>早いもの勝ち！</strong>
							</p>
						<?php endif; ?>
					</div><!-- //.ideas_statistic -->

					<hr />

					<div class="more">
						<?php comments_template() ?>
					</div>

					<?php get_template_part( 'parts/share' ) ?>



				</article><!-- //.single-container -->

			<?php endwhile; endif; ?>

			<div class="col-xs-12 col-sm-3" id="sidebar" role="navigation">
				<?php get_sidebar( 'ideas' ) ?>
			</div>

		</div><!-- //.row-offcanvas -->
	</div><!-- //.container -->

<?php get_footer();
