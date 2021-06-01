<li data-post-id="<?php the_ID(); ?>" <?php post_class( 'media tweet--loop' ); ?>>

	<a href="<?php the_permalink(); ?>" class="tweet__link--loop">
		<div class="tweet__author">
			<h2 class="tweet__author--header clearfix">
				<?php echo get_avatar( get_the_author_meta( 'ID' ), 96, '', get_the_author(), [ 'class' => 'avatart img-circle' ] ); ?>
				<span class="tweet__author--name"><?php the_author(); ?></span>
				<small><?php echo hametuha_user_role( get_the_author_meta( 'ID' ) ); ?></small>
				<br/>
				<small class="tweet__date text-muted">
					<i class="icon-calendar"></i> <?php the_time( get_option( 'date_format' ) . ' H:i' ); ?>
				</small>
			</h2>
		</div>

		<div class="tweet__content tweet__content--loop">
			<?php if ( ! \Hametuha\Model\Anpis::get_instance()->is_tweet() ) : ?>
				<h2 class="tweet__content--title"><?php the_title(); ?></h2>
			<?php endif; ?>
			<?php the_excerpt(); ?>
		</div>

		<div class="tweet__meta">
			<span class="tweet__comment">
				<i class="icon-bubble"></i> | <?php comments_number( '0', '1', '%' ); ?>
			</span>
			<span class="tweet__mentions">
				@ |
				<?php if ( $post->mention_to ) : ?>
					<?php foreach ( $post->mention_to as $user ) : ?>
						<span
						   class="help-tip"
						   title="<?php echo esc_attr( $user->display_name ); ?>">
							<?php
							echo get_avatar(
								$user->ID,
								32,
								'',
								$user->display_name,
								[
									'title' => $user->display_name,
									'class' => 'img-circle avatar tweet__mentions--img',
								]
							)
							?>
						</span>
					<?php endforeach; ?>
				<?php else : ?>
					-
				<?php endif; ?>
			</span>
		</div>
	</a>

</li>
