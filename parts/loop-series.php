<li <?php post_class( 'media loop-series' ); ?>>
	<a class="media__link<?php echo ! has_post_thumbnail() ? ' media__link--nopad' : ''; ?>" href="<?php the_permalink(); ?>">

		<?php
		if ( has_post_thumbnail() ) {
			$style = sprintf( "background-image: url('%s')", wp_get_attachment_image_src( get_post_thumbnail_id(), 'medium' )[0] );
			echo <<<HTML
				<div class="pseudo-thumbnail" style="{$style}"></div>
HTML;
		}
		?>

		<div class="media-body">

			<!-- Title -->
			<h2>
				<?php the_title(); ?>
				<small>作品集</small>
				<?php if ( is_series_finished() ) : ?>
					<span class="label label-danger">完結</span>
				<?php endif; ?>
			</h2>

			<!-- Post Data -->
			<ul class="list-inline">
				<li class="author-info">
					<?php echo get_avatar( get_the_author_meta( 'ID' ), 40 ); ?>
					<?php the_author(); ?> 編
				</li>
				<li class="date">
					<i class="icon-calendar2"></i> <?php the_series_range(); ?>
				</li>
				<li>
					<i class="icon-books"></i> <?php echo number_format_i18n( get_post_children_count() ); ?>作収録
				</li>
				<li class="static">
					<i class="icon-reading"></i> <?php the_post_length( '全', '文字', '計測不能' ); ?>
				</li>
			</ul>

			<!-- Excerpt -->
			<div class="archive-excerpt">
				<?php if ( 2 == get_post_meta( get_the_ID(), '_kdp_status', true ) ) : ?>
					<span class="label label-warning">Amazonで販売中！</span>
				<?php endif; ?>
				<p class="text-muted"><?php echo trim_long_sentence( get_the_excerpt(), 98 ); ?></p>
			</div>
		</div>

	</a>
</li>
