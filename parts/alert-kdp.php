<?php
$series = Hametuha\Model\Series::get_instance();

$series_id = 0;
if ( 'series' == get_post_type() ) {
	$series_id = get_the_ID();
} else {
	$series_id = $post->post_parent;
}
$limit = $series->get_visibility( $series_id );
if ( is_numeric( $limit ) || is_array( $limit ) ) {
	$asin      = $series->get_asin( $series_id );
	$permalink = get_permalink( $series_id );
	$title     = get_the_title( $series_id );
	if ( is_array( $limit ) ) {
		sort( $limit );
		$msg = sprintf( '%sは%sを無料で読むことができます。', esc_html( $title ), implode( '、', array_map( function( $number ) {
			return sprintf( '%d話', $number );
		}, $limit ) ) );
	} elseif ( $limit ) {
		$msg = sprintf( '%sは%d話まで無料で読むことができます。', esc_html( $title ), number_format( $limit ) );
	} else {
		$msg = sprintf( '%sの全文は電子書籍でご覧頂けます。', esc_html( $title ) );
	}
	switch ( $series->get_status( $series_id ) ) {
		case 2:
			?>
			<div class="series__row--single text-center">
				<a href="<?php echo get_permalink( $series_id ); ?>">
					<img src="<?php echo wp_get_attachment_image_src( get_post_thumbnail_id( $series_id ), 'medium' )[0]; ?>"
						 alt="<?php echo esc_attr( $title ); ?>" class="series__single--image"/>
				</a>
				<p class="text-muted">
					<?php echo $msg; ?>
					続きはAmazonでご利用ください。
				</p>
				<a class="btn btl-lg btn-trans btn-amazon" href="<?php echo $series->get_kdp_url( $series_id ); ?>"
				   data-outbound="kdp"
				   data-action="<?php echo esc_attr( $series->get_asin( $series_id ) ); ?>"
				   data-label="<?php the_ID(); ?>"
				   data-value="<?php echo get_series_price( $series_id ); ?>">
					<i class="icon-amazon"></i> Amazonへ行く
				</a>
			</div>
			<?php
			break;
		case 1:
			?>
			<div class="alert alert-danger text-center">
				<p>
					<?php echo $msg; ?>
					続きは現在販売準備中です。乞うご期待。
				</p>
			</div>
			<?php
			break;
		default:
			// Do nothing
			break;
	}
}
