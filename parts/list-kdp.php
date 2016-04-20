<?php
$series = \Hametuha\Model\Series::get_instance();
if ( 'kdp' === get_query_var( 'meta_filter' ) ) {
	global $wp_query;
	$query = $wp_query;
} else {
	$query = new WP_Query( [
		'post_type'      => 'series',
		'post_status'    => 'publish',
		'meta_filter'    => 'kdp',
		'posts_per_page' => 12,
	] );
}
?>
<div class="ebookList<?= is_front_page() ? ' ebookList--front' : '' ?>">
	<div class="container">
		<div class="row ebookList__wrap">
			<?php
			while ( $query->have_posts() ) {
				$query->the_post();
				?>
				<div class="col-xs-4 col-sm-3 col-md-3 col-lg-2 ebookList__item">
					<div class="thumbnail ebookList__thumbnail">
						<a href="<?php the_permalink() ?>">
							<?php the_post_thumbnail( 'medium', [ 'alt' => get_the_title() ] ) ?>
						</a>

						<div class="caption">
							<p class="text-center">
								<a class="btn btn-primary btn-sm hidden-xs" href="<?php the_permalink() ?>">詳しく</a>
								<a data-outbound="kdp"
								   data-action="<?= esc_attr( $series->get_asin( get_the_ID() ) ) ?>"
								   data-label="<?php the_ID() ?>" data-value="<?= get_series_price() ?>"
								   class="btn btn-amazon btn-sm" href="<?= $series->get_kdp_url( get_the_ID() ) ?>"><i
										class="icon-amazon"></i> 購入</a>
							</p>
						</div>

					</div>
				</div>
				<?php
			}
			wp_reset_postdata();
			?>
		</div><!-- //.row -->
		<div class="row">
			<p>
				<a href="<?= home_url( '/kdp/') ?>" class="btn btn-amazon btn-lg btn-block">
					<i class="icon-amazon"></i> 電子書籍一覧を見る
				</a>
			</p>
		</div>
	</div>
</div>