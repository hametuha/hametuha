<!-- post thumbnail -->
<?php if ( has_post_thumbnail() ) : ?>
	<div class="post-title-thumbnail">
		<?php the_post_thumbnail( 'large', array( 'itemprop' => 'image' ) ); ?>
		<?php
		$thumbnail = get_post( get_post_thumbnail_id() );
		if ( ! empty( $thumbnail->post_excerpt ) ) :
			?>
			<?php echo wpautop( $thumbnail->post_excerpt ); ?>
		<?php endif; ?>
	</div>
<?php endif; ?>
