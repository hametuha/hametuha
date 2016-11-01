<?php
if ( is_preview() ) {
	return;
}
?>
<div class="series__row series__row--share shareContainer__wrapper">

	<div class="container series__inner">

		<div class="row">
			<div class="col-xs-12">

				<h2 class="series__title--share text-center">
					<small class="series__title--caption">Share This</small>
					シェアする
				</h2>
				<p class="text-muted text-center">
					面白かったり、気になったらSNSでシェアしてください。<br />
					シェアしていただくと作者がやる気を出します。
				</p>

			</div>
		</div>

		<?php get_template_part( 'parts/share' ) ?>

	</div>
</div>
<!-- //.series__row--share -->
