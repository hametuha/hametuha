<?php
/**
 * 安否情報をカードレイアウトで表示する
 *
 * @feature-group anpi
 * @var array $args
 */
$args = wp_parse_args( [
	'type' => 'card',
] );
$anpi = \Hametuha\Model\Anpis::get_instance();
?>
<div data-post-id="<?php the_ID(); ?>" class="col-sm-6 col-md-4 mb-4">

	<div class="card card-idea card-list-item">

		<div class="card-body">

			<!-- Title -->
			<div class="mb-3 d-flex justify-content-between align-items-start">
				<h2 class="h4 card-title">
					<?php
						$title = $anpi->is_tweet() ? sprintf(
						esc_html__( '%sさんの安否報告#%d', 'hametuha' ),
						get_the_author( 'display_name' ),
							get_the_ID()
						) : get_the_title();
					?>
					<a href="<?php the_permalink(); ?>"><?php echo esc_html( $title ); ?></a>
				</h2>
			</div>


			<?php
			$terms = get_the_terms( get_the_ID(), 'anpi_cat' );
			if ( $terms && ! is_wp_error( $terms ) ) : ?>
				<p>
					<?php
					echo implode( ' ', array_map( function ( $term ) {
						return sprintf(
							'<a href="%s" class="term-link term-link-sm">#%s</a>',
							esc_url( get_term_link( $term ) ),
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
		</div>
	</div><!-- .card -->
</div>
