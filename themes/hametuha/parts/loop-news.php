<li class="news-list__item">
	<a href="<?php the_permalink(); ?>" class="news-list__link clearfix">
		<?php if ( has_post_thumbnail() ) : ?>
			<div class="news-list__image">
				<?php the_post_thumbnail( 'thumbnail', [ 'class' => 'news-list__thumbnail' ] ); ?>
			</div>
		<?php endif ?>

		<div class="news-list__body">


			<h4 class="news-list__title">
				<?php if ( hamenew_is_pr() ) : ?>
					<small class="news-list__pr">【PR】</small>
				<?php endif; ?>
				<?php the_title(); ?>
			</h4>

			<p class="news-list__meta">

				<span class="news-list__time">
					<i class="icon-clock"></i> <?php echo hametuha_passed_time( $post->post_date ); ?>
				</span>

				<?php if ( ( $terms = get_the_terms( get_post(), 'genre' ) ) && ! is_wp_error( $terms ) ) : ?>

					<span class="news-list__genre">
						<i class="icon-tag5"></i> 
						<?php
						echo implode( ', ', array_map( function ( $term ) {
							return esc_html( $term->name );
						}, $terms ) );
						?>
					</span>

				<?php endif; ?>

			</p>
		</div><!-- //.news-list__body -->
	</a>
</li>
