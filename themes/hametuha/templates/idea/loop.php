<?php
/**
 * アイデアのリスト用テンプレート
 *
 * @feature-group idea
 */
$ideas = \Hametuha\Model\Ideas::get_instance();
?>
<div data-post-id="<?php the_ID(); ?>" class="col-sm-6 col-md-4 mb-4">

	<div class="card card-idea card-list-item shadow">

		<div class="card-body">

			<!-- Title -->
			<div class="mb-3 d-flex justify-content-between align-items-start">
				<h2 class="h4 card-title">
					<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				</h2>
				<span class="idea-status">
					<?php if ( ! in_array( get_post_status(), [ 'publish', 'future' ], true )  ) : ?>
						<i class="icon-lock" title="<?php esc_attr_e( '非公開', 'hametuha' ); ?>"></i>
					<?php endif; ?>
					<?php if ( is_user_logged_in() && $ideas->is_stocked( get_current_user_id(), get_the_ID() ) ) : ?>
						<i class="icon-heart idea-stocked" title="<?php esc_attr_e( 'ストック済み', 'hametuha' ); ?>"></i>
					<?php endif; ?>
				</span>
			</div>


			<?php
			$terms = get_the_tags( get_the_ID() );
			if ( $terms && ! is_wp_error( $terms ) ) : ?>
				<p>
					<?php
					echo implode( ' ', array_map( function ( $term ) {
						$idea_term_url = add_query_arg( [
							'tag' => $term->slug,
						], get_post_type_archive_link( 'ideas' ) );
						return sprintf(
							'<a href="%s" class="term-link term-link-sm">#%s</a>',
							esc_url( $idea_term_url ),
							esc_html( $term->name )
						);
					}, $terms ) );
					?>
				</p>
			<?php endif; ?>

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

		<div class="card-footer d-flex justify-content-between text-muted">
			<span>
				<?php echo hametuha_passed_time( $post->post_date ); ?>
			</span>
			<span>
				<i class="icon-heart<?php echo $post->stock ? ' idea-stocked ' : '' ?>"></i>
				<?php
				printf(
					esc_html__( '%s人がストック', 'hametuha' ),
					number_format( $post->stock )
				);
				?>
			</span>
		</div>
	</div><!-- .card -->
</div>
