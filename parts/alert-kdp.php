<?php
$series = Hametuha\Model\Series::get_instance();

$series_id = 0;
if ( 'series' == get_post_type() ) {
	$series_id = get_the_ID();
} else {
	$series_id = $post->post_parent;
}
$limit = $series->get_visibiity( $series_id );
if ( $limit ) {
	$asin      = $series->get_asin( $series_id );
	$permalink = get_permalink( $series_id );
	$title     = get_the_title( $series_id );
	$msg       = <<<HTML
		        	<a class="alert-link" href="{$permalink}">
HTML;
	switch ( $series->get_status( $series_id ) ) {
		case 2:
			?>
			<div class="series__row--single text-center">
				<a href="<?= get_permalink( $series_id ) ?>">
					<img src="<?= wp_get_attachment_image_src( get_post_thumbnail_id( $series_id ), 'medium' )[0] ?>"
					     alt="<?= esc_attr( $title ) ?>" class="series__single--image"/>
				</a>
				<p class="text-muted">
					<?= esc_html( $title ) ?>は<?= number_format( $limit ) ?>話まで無料で読むことができます。
					続きはAmazonでご利用ください。
				</p>
				<a class="btn btl-lg btn-trans btn-amazon" href="<?= $series->get_kdp_url( $series_id ) ?>"
				   data-outbound="kdp"
				   data-action="<?= esc_attr( $series->get_asin( $series_id ) ) ?>"
				   data-label="<?php the_ID() ?>"
				   data-value="<?= get_series_price( $series_id ) ?>">
					<i class="icon-amazon"></i> Amazonへ行く
				</a>
			</div>
			<?php
			break;
		case 1:
			?>
			<div class="alert alert-danger text-center">
				<p>
					<?= esc_html( $title ) ?>は<?= number_format( $limit ) ?>話まで無料で読むことができます。
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