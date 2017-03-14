<?php
/** @var stdClass $schema */
/** @var string  $url */
?>
<div class="embed-wrap">

		<?php if ( $schema->image ) : ?>
			<div class="embed-thumbnail">
				<a href="<?php $url ?>" class="embed-thumbnail-link">
					<img src="<?= $schema->image ?>" alt="<?= esc_attr( $schema->name ) ?>" class="embed-thumbnail-img" />
				</a>
			</div>
		<?php endif; ?>

		<div class="embed-content">

			<h3 class="embed-title">
				<a href="<?= esc_url( $url ) ?>"><?= esc_html( $schema->name ) ?></a>
				<span class="embed-title-price">
					&yen;<?= number_format( $schema->offers->price ) ?>
				</span>
			</h3>

			<div class="embed-author">
				<i class="icon-ha"></i> <span class="embed-author-name"><?= esc_html( $schema->brand->name ) ?></span>
			</div>
			<div class="embed-excerpt">
				<?= wpautop( strip_tags( $schema->description ) ) ?>
			</div>
			<div class="embed-action">
				<a class="btn btn-primary" href="<?= esc_url( $url ) ?>">ショップへ</a>
				<?php ?>

				<?php ?>
			</div>
		</div>

		<div class="embed-footer">

			<span class="embed-footer-label">
				商品
			</span>

			<span class="embed-credit">
				<a class="embed-credit-link" href="<?= esc_url( $url ) ?>">
					minico.me
				</a>
			</span>

		</div>

</div>
