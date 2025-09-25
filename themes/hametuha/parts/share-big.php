<?php
/**
 * シェアボタンの見出しつき
 *
 * @var array{title:string, desc:string} $args
 */
if ( 'publish' !== ( get_queried_object()->post_status ?? '' ) ) {
	return;
}
$args = wp_parse_args( $args, [
	'title' => 'シェアする',
	'desc'  => '面白かったり、気になったらSNSでシェアしてください。<br />シェアしていただくと作者がやる気を出します。'
] );
?>
<div class="series__row series__row--share shareContainer__wrapper">

	<div class="container series__inner">

		<div class="row">
			<div class="col-xs-12">

				<h2 class="series__title--share text-center">
					<small class="series__title--caption">Share This</small>
					<?php echo esc_html( $args['title'] ); ?>
				</h2>
				<p class="text-muted text-center">
					<?php echo wp_kses_post( $args['desc'] ); ?>
				</p>
			</div>
		</div>

		<?php get_template_part( 'parts/share' ); ?>

	</div>
</div>
<!-- //.series__row--share -->
