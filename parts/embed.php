<?php
/**
 * Embed template for oEmbed self.
 *
 * @see get_template_part()
 * @var array $args See `get_template_part()` for detail.
 */

$object = $args['object'] ?? null;
$url    = $args['url'] ?? '';

$model       = \Hametuha\Model\Series::get_instance();
$epub_status = $model->get_status( $object->ID );
?>
<div class="embed-wrap">

	<div class="embed-body">
		<?php if ( has_post_thumbnail( $object ) ) : ?>
			<div class="embed-thumbnail">
				<a href="<?php the_permalink( $object ); ?>" class="embed-thumbnail-link">
					<?php echo get_the_post_thumbnail( $object, 'medium', [ 'class' => 'embed-thumbnail-img' ] ); ?>
				</a>
			</div>
		<?php endif; ?>

		<div class="embed-content">

			<h3 class="embed-title">
				<a href="<?php the_permalink( $object ); ?>"><?php echo esc_html( get_the_title( $object ) ); ?></a>
			</h3>

			<div class="embed-author">
				<?php $name = get_the_author_meta( 'display_name', $object->post_author ); ?>
				<?php echo get_avatar( $object->post_author, 64, '', $name, [ 'class' => 'embed-author-img' ] ); ?>
				<span class="embed-author-name"><?php echo esc_html( $name ); ?></span>
			</div>
			<div class="embed-excerpt">
				<?php echo wpautop( get_the_excerpt( $object ) ); ?>
			</div>
			<div class="embed-action">
				<a class="btn btn-primary" href="<?php the_permalink( $object ); ?>">読む</a>
				<?php if ( 2 === $epub_status ) : ?>
					<a class="btn btn-amazon" href="<?php echo $model->get_kdp_url( $object->ID ); ?>"><i
								class="icon-amazon"></i> Amazonで見る</a>
				<?php endif; ?>
			</div>
		</div>

	</div>

	<div class="embed-footer">

		<span class="embed-footer-label">
			<?php
			switch ( $object->post_type ) {
				case 'post':
					$label = '作品';
					foreach ( get_the_category( $object ) as $cat ) {
						$label = $cat->name;
					}
					break;
				case 'series':
					if ( 2 === $epub_status ) {
						$label = '電子書籍';
					} else {
						$label = '連載';
					}
					break;
				default:
					$label = get_post_type_object( $object->post_type )->label;
					break;
			}
			echo esc_html( $label );
			?>
		</span>

		<span class="embed-credit">
			<i class="icon-ha"></i>
			<a class="embed-credit-link" href="<?php the_permalink( $object ); ?>">
				<?php echo esc_html( hametuha_grab_domain( $url ) ); ?>
			</a>
		</span>

	</div>

</div>
