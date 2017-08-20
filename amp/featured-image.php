<?php
$featured_image = $this->get( 'featured_image' );

if ( empty( $featured_image ) ) {
	return;
}

$amp_html = $featured_image['amp_html'];
$caption = $featured_image['caption'];
?>
<figure class="amp-wp-article-featured-image wp-caption">
	<?php echo $amp_html; // amphtml content; no kses ?>
	<?php if ( $caption ) : ?>
		<fig-caption class="wp-caption-text">
			<?php echo wp_kses_data( $caption ); ?>
		</fig-caption>
	<?php endif; ?>
</figure>

<div class="amp-hametuha-excerpt">
	<?php the_excerpt() ?>
</div>

<div class="amp-ad-container">
	<amp-ad
			type="adsense"
			data-ad-client="ca-pub-0087037684083564"
			data-ad-slot="9464744841"
			width="320"
			height="100">
	</amp-ad>
</div>
