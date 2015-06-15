<div class="row row--share shareContainer" data-share-url="<?= admin_url('admin-ajax.php?action=hametuha_share_count&post_id='.get_the_ID(), is_ssl() ? 'https' : 'http') ?>">
	<?php foreach( hametuha_share() as $brand => $link ): ?>
	<div class="col-xs-4 col-sm-2 text-center chareContainer__item">
		<?= $link ?>
	</div>
	<?php endforeach; ?>
</div>
