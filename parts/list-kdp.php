<?php
$series = \Hametuha\Model\Series::get_instance();

if ( 'kdp' === get_query_var( 'meta_filter' ) ) {
	global $wp_query;
	$query = $wp_query;
} elseif ( $doujin = is_doujin_profile_page() ) {
	$query = new WP_Query( [
		'post_type'      => 'series',
		'post_status'    => 'publish',
		'meta_query'     => [
			[
				'key'   => '_kdp_status',
				'value' => 2,
			],
		],
		'author_name'    => $doujin,
		'posts_per_page' => -1,
		'orderby'        => [
			'menu_order' => 'DESC',
			'date'       => 'DESC',
		],
	] );
} else {
	$query = new WP_Query( [
		'post_type'      => 'series',
		'post_status'    => 'publish',
		'meta_query'     => [
			[
				'key'   => '_kdp_status',
				'value' => 2,
			],
		],
		'posts_per_page' => 16,
		'orderby'        => [
			'menu_order' => 'DESC',
			'date'       => 'DESC',
		],
	] );
}
if ( ! $query->have_posts() ) {
	return;
}
?>
<div class="ebookList<?php echo ( is_front_page() || is_doujin_profile_page() ) ? ' ebookList--front' : ''; ?>">
	<div class="container">
		<div class="ebookList__wrap">
			<?php
			while ( $query->have_posts() ) {
				$query->the_post();
				?>
				<div class="ebookList__item">
					<div class="thumbnail ebookList__thumbnail">
						<a href="<?php the_permalink(); ?>">
							<?php the_post_thumbnail( 'medium', [ 'alt' => get_the_title() ] ); ?>
						</a>

						<div class="caption">
							<p class="text-center">
								<a data-outbound="kdp"
								   data-action="<?php echo esc_attr( $series->get_asin( get_the_ID() ) ); ?>"
								   data-label="<?php the_ID(); ?>" data-value="<?php echo get_series_price(); ?>"
								   class="btn btn-amazon btn-sm" href="<?php echo $series->get_kdp_url( get_the_ID() ); ?>"><i
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
		<div class="row ebookList__button">
			<div class="col-xs-10 col-xs-offset-1">
				<a href="<?php echo home_url( '/kdp/' ); ?>" class="btn btn-amazon btn-lg btn-block">
					<i class="icon-amazon"></i> 電子書籍一覧を見る
				</a>
			</div>
		</div>
	</div>
</div>
