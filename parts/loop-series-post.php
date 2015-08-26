<?php
global $series_counter;
if( !$series_counter ){
	$series_counter = 0;
}
$series_counter++;
?>
<li data-post-id="<?php the_ID() ?>" <?php post_class( 'series__item col-xs-12 col-sm-4 post-in-series' ) ?>>

	<span class="series__item--counter">
		<?= number_format( $series_counter ) ?>
	</span>

	<a href="<?php the_permalink() ?>" class="series__item--link">

		<?php if ( has_post_thumbnail() ) :
			$style = sprintf("background-image: url('%s')", wp_get_attachment_image_src(get_post_thumbnail_id(), 'medium')[0]);
			echo <<<HTML
				<div class="series__item--thumbnail" style="{$style}"></div>
HTML;
		endif; ?>

		<div class="series__item--body">

			<!-- Title -->
			<h3 class="series__item--title">
				<span><?php the_title(); ?></span>
				<?php
				echo implode( ' ', array_map( function ( $term ) {
					printf( '<small>%s</small>', esc_html( $term->name ) );
				}, get_the_category() ) );
				?>
			</h3>

			<!-- Post Data -->
			<ul class="list-inline series__item--info">
				<li class="author-info">
					<?= get_avatar( get_the_author_meta( 'ID' ), 32 ); ?>
					<?php the_author(); ?>
				</li>
				<li class="date">
					<i class="icon-calendar2"></i> <?= hametuha_passed_time( $post->post_date ) ?>
					<?php if ( is_recent_date( $post->post_date, 3 ) ): ?>
						<span class="label label-danger">New!</span>
					<?php elseif ( is_recent_date( $post->post_modified, 7 ) ): ?>
						<span class="label label-info">更新</span>
					<?php endif; ?>
				</li>
				<li class="static"><i class="icon-reading"></i> <?= number_format( get_post_length() ) ?>文字</li>
			</ul>

			<!-- Excerpt -->
			<div class="series__item--excerpt">
				<?php the_excerpt() ?>
			</div>


		</div>
	</a>
</li>
