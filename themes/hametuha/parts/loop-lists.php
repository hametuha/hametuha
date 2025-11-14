<?php
/**
 * ループ用のテンプレート
 *
 * @feature-group list
 *
 */
$is_mine = ( get_current_user_id() === (int) get_the_author_meta( 'ID' ) );
?>
<div data-post-id="<?php the_ID(); ?>" class="col-sm-6 col-md-4 mb-4">

	<div class="card shadow card-lists card-list-item<?php echo esc_attr( $is_mine ? ' bg-primary text-white' : '' ); ?>">

		<div class="card-body">

			<!-- Title -->
			<div class="mb-3 d-flex justify-content-between align-items-start">
				<h2 class="h4 card-title">
					<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				</h2>
				<?php if ( 'publish' !== get_post_status() ) : ?>
					<span class="idea-status">
						<i class="icon-lock" title="<?php esc_attr_e( '非公開', 'hametuha' ); ?>"></i>
					</span>
				<?php elseif ( is_recommended() ) : ?>
					<span class="idea-status">
						<i class="icon-star3 text-warning" title="<?php esc_attr_e( 'オススメ', 'hametuha' ); ?>"></i>
					</span>
				<?php endif; ?>
			</div>

			<p class="card-text idea-excerpt">
				<?php echo esc_html( get_the_excerpt() ); ?>
			</p>

			<p class="author-info">
				<?php
				echo get_avatar( get_the_author_meta( 'ID' ), 40, '', get_the_author_meta( 'display_name' ), [
					'class' => 'img-circle',
				] );
				?>
				<span>
					<?php the_author(); ?>
				</span>
			</p>
		</div><!-- .card-body -->

		<div class="card-footer d-flex justify-content-between <?php echo $is_mine ? '' : 'text-muted'; ?>">
			<span>
				<?php echo hametuha_passed_time( $post->post_date ); ?>
			</span>
			<span>
				<i class="icon-books"></i> <?php echo number_format_i18n( $post->num_children ); ?>作収録
			</span>
		</div>
	</div><!-- .card -->
</div>
