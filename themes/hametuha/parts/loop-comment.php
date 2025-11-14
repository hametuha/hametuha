<?php
/**
 * トップページ用途コメントテンプレート
 */
?>
<li <?php post_class( 'media media--pad' ); ?>>

	<a class="media__link media__link--nopad" href="<?php echo get_comment_link( $post->ID ); ?>">


		<div class="pull-left comment-face">
			<?php echo get_avatar( get_the_author_meta( 'ID' ), 120 ); ?>
		</div>

		<div class="media-body">

			<!-- Title -->
			<h2 class="comment-title">
				<?php the_title(); ?> <small>へのコメント <i class="icon-bubble"></i></small>
			</h2>

			<!-- Post Data -->
			<ul class="list-inline">
				<li>
					<span class="label label-info"><?php echo get_post_type_object( get_post_type( $post->post_parent ) )->labels->name; ?></span>
				</li>
				<li class="author-info">
					<?php the_author(); ?>
				</li>
				<li class="date">
					<i class="icon-calendar2"></i> <?php echo hametuha_passed_time( $post->post_date ); ?>
					<?php if ( is_recent_date( $post->post_date, 3 ) ) : ?>
						<span class="label label-danger">New!</span>
					<?php endif; ?>
				</li>
			</ul>

			<!-- Excerpt -->
			<div class="archive-excerpt">
				<p class="text-muted"><?php echo trim_long_sentence( strip_tags( $post->post_content ), 80 ); ?></p>
			</div>

		</div>

	</a>

</li>
