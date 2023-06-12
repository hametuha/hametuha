<?php
/** @var stdClass $schema */
/** @var string  $url */
?>
<div class="embed-wrap embed-minicome">

	<div class="embed-body">

		<?php if ( $schema->image ) : ?>
			<div class="embed-thumbnail">
				<a href="<?php $url; ?>" class="embed-thumbnail-link">
					<img src="<?php echo $schema->image; ?>" alt="<?php echo esc_attr( $schema->name ); ?>" class="embed-thumbnail-img" />
				</a>
			</div>
		<?php endif; ?>

		<div class="embed-content">

			<h3 class="embed-title">
				<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $schema->name ); ?></a>
				<span class="embed-title-price">
					&yen;<?php echo number_format( $schema->offers->price ); ?>
				</span>
			</h3>

			<div class="embed-author">
				<i class="icon-ha"></i> <span class="embed-author-name"><?php echo esc_html( $schema->brand->name ); ?></span>
			</div>
			<div class="embed-excerpt">
				<?php echo wpautop( strip_tags( $schema->description ) ); ?>
			</div>
			<div class="embed-action">
				<a class="btn btn-primary" href="<?php echo esc_url( $url ); ?>">ショップへ</a>
			</div>
		</div>
	</div>

	<div class="embed-footer">

		<span class="embed-footer-label">
			商品
		</span>

		<span class="embed-credit">
			<a class="embed-credit-link" href="<?php echo esc_url( $url ); ?>">
				minico.me
			</a>
		</span>

	</div>

</div>
