<?php
/**
 * スレッドのループ
 *
 * @feature-group thread
 */
?>
<li <?php post_class( 'media' ); ?>>
	<a class="media__link media__link--nopad" href="<?php the_permalink(); ?>">

		<div class="pull-left">
			<?php echo get_avatar( get_the_author_meta( 'ID' ), 160 ); ?>
		</div>

		<div class="media-body">
			<h3 class="title">
				<?php echo hametuha_censor( get_the_title() ); ?>
				<small><i class="icon-calendar"></i> <?php echo hametuha_passed_time( $post->post_date ); ?></small>
			</h3>
			<ul class="list-inline">
				<li><i class="icon-tags"></i>
					<?php
						echo implode(' ', array_map(function ( $term ) {
							return esc_html( $term->name );
						}, get_the_terms( $post, 'topic' )));
						?>
				</li>
				<li>
					<i class="icon-user"></i> <?php the_author(); ?>
				</li>
				<li>
					<i class="icon-bubbles3"></i> レス <?php echo ( $number = get_comments_number() ); ?>件
				</li>
				<li>
					<i class="icon-clock"></i> 最新レス
					<?php
					$latest = hamethread_get_latest_comment_date();
					echo $latest ? mysql2date( 'Y/n/j', $latest ) : 'なし';
					if ( hamethread_recently_commented() ) :
						?>
						<span class="label label-success">New!</span>
					<?php endif; ?>
				</li>
			</ul>

			<div class="archive-excerpt text-muted">
				<?php the_excerpt(); ?>
			</div><!-- .excerpt -->
		</div>

	</a>

</li>
