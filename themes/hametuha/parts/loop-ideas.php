<li data-post-id="<?php the_ID(); ?>" <?php post_class( 'media' ); ?>>

	<a href="<?php the_permalink(); ?>" class="media__link media__link--nopad">

		<div class="media-body">

			<!-- Title -->
			<h2 class="media-body__title">
				<small>アイデア: </small><?php the_title(); ?>
			</h2>

			<!-- Post Data -->
			<ul class="list-inline">
				<li class="author-info">
					<?php echo get_avatar( get_the_author_meta( 'ID' ), 40 ); ?>
					<?php the_author(); ?>
				</li>
				<li>
					<i class="icon-tags"></i>
					<?php if ( ( $terms = get_the_tags( get_the_ID() ) ) && ! is_wp_error( $terms ) ) : ?>
						<?php
						echo implode( ', ', array_map( function ( $term ) {
							return esc_html( $term->name );
						}, $terms ) );
						?>
					<?php else : ?>
						<span class="text-muted">分類なし</span>
					<?php endif; ?>
				</li>
				<li class="date">
					<i class="icon-calendar2"></i> <?php echo hametuha_passed_time( $post->post_date ); ?>
					<?php if ( is_recent_date( $post->post_date, 3 ) ) : ?>
						<span class="label label-danger">New!</span>
					<?php elseif ( is_recent_date( $post->post_modified, 7 ) ) : ?>
						<span class="label label-info">更新</span>
					<?php endif; ?>
				</li>
				<?php if ( in_array( $post->post_status, [ 'private', 'protected' ] ) ) : ?>
				<li>
					<span class="label label-default"><?php echo esc_html( get_post_status_object( get_post_status() )->label ); ?></span>
				</li>
				<?php endif; ?>
			</ul>

			<!-- Excerpt -->
			<div class="archive-excerpt">
				<?php if ( $post->stock ) : ?>
					<p class="text-muted"><?php echo numbeR_format( $post->stock ); ?>人がこのアイデアをストックしています。</p>
				<?php else : ?>
					<p class="text-warning">まだ誰もこのアイデアをストックしていません。早い者勝ち！</p>
				<?php endif; ?>
			</div>


		</div>
	</a>
</li>
