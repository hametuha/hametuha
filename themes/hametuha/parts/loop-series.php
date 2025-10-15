<?php
/**
 * 連載ページの掲載作
 *
 * @feature-group series
 */

$has_kdp = 2 === (int) get_post_meta( get_the_ID(), '_kdp_status', true );
$class = [ 'loop-series', 'shadow-sm' ];
if ( $has_kdp ) {
	$class[] = 'has-kdp';
}
?>
<li <?php post_class( implode( ' ', $class ) ); ?>>
	<a class="loop-series__link" href="<?php the_permalink(); ?>">

		<figure class="loop-series__cover">
			<?php
			if ( has_post_thumbnail() ) {
				the_post_thumbnail( 'medium' );
			} else {
				printf(
					'<img src="%s/assets/img/covers/printing.png" alt="" />',
					get_template_directory_uri()
				);
			}
			?>
		</figure>

		<div class="loop-series__body">

			<!-- Title -->
			<h2 class="loop-series__title">
				<?php the_title(); ?>
			</h2>

			<!-- Post Data -->
			<ul class="loop-series__metas">
				<li class="loop-series__meta loop-series__meta--author">
					<?php echo get_avatar( get_the_author_meta( 'ID' ), 40 ); ?>
					<?php the_author(); ?> 編
				</li>
				<li class="loop-series__meta loop-series__meta--date">
					<i class="icon-calendar2"></i> <?php the_series_range(); ?>
				</li>
				<li class="loop-series__meta loop-series__meta--volume d-flex justify-content-between align-center">
					<span>
						<i class="icon-books"></i>
						<?php echo number_format_i18n( get_post_children_count() ); ?>作
						（<?php the_post_length( '全', '文字', '文字数不明' ); ?>）
					</span>
					<?php if ( is_series_finished() ) : ?>
						<span class="badge rounded-pill text-bg-primary">完結</span>
					<?php else: ?>
						<span class="badge rounded-pill text-bg-secondary">連載中</span>
					<?php endif; ?>
				</li>
			</ul>


			<!-- Excerpt -->
			<div class="archive-excerpt">
				<p><?php echo trim_long_sentence( get_the_excerpt(), 98 ); ?></p>
			</div>
		</div>
	</a>

	<?php
	if ( $has_kdp ) : ?>
		<div class="loop-series__kdp">
			<a class="btn btn-outline-primary" href="<?php hametuha_the_kdp_url(); ?>">
				Amazonで販売中 <i class="icon-newtab"></i>
			</a>
		</div>
	<?php endif; ?>
</li>
