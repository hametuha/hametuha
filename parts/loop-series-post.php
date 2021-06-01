<?php
/**
 * 作品集ページで呼び出される
 *
 * @package hametuha
 * @var int $counter
 */
?>
<li data-post-id="<?php the_ID(); ?>" <?php post_class( 'series__item post-in-series' ); ?>>

	<a href="<?php the_permalink(); ?>" class="series__item--link">


		<div class="series__item--body">

			<!-- Title -->
			<div class="series__item--meta clearfix">
				<span class="series__item--author"><?php the_author(); ?></span>
				<h3 class="series__item--title">
					<span class="series__item--counter"><?php echo number_format( $counter ); ?>.</span>
					<?php the_title(); ?>
				</h3>
			</div>

			<!-- Post Data -->
			<ul class="list-inline series__item--info">
				<li>
					<?php
					echo implode(
						' ',
						array_map(
							function ( $term ) {
								printf( '<span class="series__item--term">%s</span>', esc_html( $term->name ) );
							},
							get_the_category()
						)
					);
					?>
				</li>
				<li>
					<span class="series__item--length"><?php echo number_format( get_post_length() ); ?>文字</span>
				</li>
				<li>
					<?php the_time( get_option( 'date_format' ) ); ?>公開
					<?php if ( is_recent_date( $post->post_date, 3 ) ) : ?>
						<span class="label label-danger">New!</span>
					<?php elseif ( is_recent_date( $post->post_modified, 7 ) ) : ?>
						<span class="label label-info">更新</span>
					<?php endif; ?>
				</li>
			</ul>

			<!-- Excerpt -->
			<div class="series__item--excerpt">
				<?php the_excerpt(); ?>
			</div>


		</div>
	</a>
</li>
