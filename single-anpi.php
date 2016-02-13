<?php get_header() ?>

<?php the_post() ?>

<div class="tweet--big">

	<div class="container">

		<article class="tweet__wrap" itemscope itemtype="http://schema.org/BlogPosting">

			<div class="tweet__author">
				<h1 class="tweet__author--header">
					<a href="<?= esc_url( home_url( '/doujin/detail/' . get_the_author_meta( 'user_nicename' ) . '/' ) ) ?>">
						<?= get_avatar( get_the_author_meta( 'ID' ), 96, '', get_the_author(), [ 'class' => 'avatart img-circle' ] ) ?>
						<span class="tweet__author--name"><?php the_author() ?></span>
						<small><?= hametuha_user_role( get_the_author_meta( 'ID' ) ) ?></small>
						<br/>
						<small class="tweet__date text-muted">
							<i class="icon-calendar"></i> <?php the_time( get_option( 'date_format' ) . ' H:i' ) ?>
						</small>
					</a>
				</h1>

				<?php if ( current_user_can( 'edit_post', get_the_ID() ) ) : ?>
					<div class="tweet__action dropdown">

						<button class="btn btn-link dropdown-toggle" type="button" id="dropdownTweet"
						        data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
							<i class="icon-cog"></i>
						</button>

						<ul class="dropdown-menu" aria-labelledby="dropdownTweet">
							<?php if ( ! \Hametuha\Model\Anpis::get_instance()->is_tweet() ) : ?>
								<li>
									<a href="<?= home_url( "/anpi/mine/edit/{$post->ID}/", 'https' ) ?>">編集</a>
								</li>
							<?php endif; ?>
							<li><a href="#">削除</a></li>
						</ul>

					</div>
				<?php endif; ?>

			</div>

			<div class="tweet__content">
				<?php if ( \Hametuha\Model\Anpis::get_instance()->is_tweet() ) : ?>
					<?php the_tweet(); ?>
				<?php else : ?>
					<h2><?php the_title() ?></h2>
				<?php endif; ?>
			</div>

			<div class="tweet__meta">
				<span class="tweet__comment">
					<i class="icon-bubble"></i> | <?php comments_number( '0', '1', '%' ) ?>
				</span>
				<span class="tweet__mentions">
				@ |
					<?php if ( $post->mention_to ) : ?>
						<?php foreach ( $post->mention_to as $user ) : ?>
							<span
								class="help-tip"
								title="<?= esc_attr( $user->display_name ) ?>">
							<?= get_avatar(
									$user->ID, 32,
									'',
									$user->display_name,
									[
									'title' => $user->display_name,
									'class' => 'img-circle avatar tweet__mentions--img',
									]
								); ?>
						</span>
						<?php endforeach; ?>
					<?php else : ?>
						-
					<?php endif; ?>
			</span>
			</div>

			<?php if ( ! \Hametuha\Model\Anpis::get_instance()->is_tweet() ) : ?>
			<div class="post-content" itemprop="articleBody">
				<?php the_content(); ?>
			</div>
			<?php endif; ?>

			<?php comments_template() ?>

		</article>


		<?php get_template_part( 'parts/share' ) ?>

	</div>

</div>
<?php get_footer() ?>
