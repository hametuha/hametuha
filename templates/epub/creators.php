<?php
/** @var WP_Post $series */
?>
<?php get_template_part('templates/epub/header') ?>

<header class="header header--creators">
	<h1 class="title">
		執筆者一覧
	</h1>
</header>

<article class="content content--creators">

	<?php foreach( $authors as $author ): /** @var WP_User $author */?>
	<div class="author-box clearfix">
		<?= get_avatar($author->ID, 300) ?>
		<h2>
			<?php if( ($ruby = get_user_meta($author->ID, 'last_name', true)) ): ?>
				<ruby>
					<?= esc_html($author->display_name) ?>
					<rt><?= esc_html($ruby) ?></rt>
				</ruby>
			<?php else: ?>
				<?= esc_html($author->display_name) ?>
			<?php endif; ?>
		</h2>
		<div class="excerpt">
			<?= wpautop($author->user_description) ?>
		</div>
		<dl class="contact clearfix">
			<dt>Webサイト</dt>
			<dd><?php
				if( $author->user_url ){
					$site_name = get_user_meta($author->ID, 'aim', true) ?: $author->user_url;
					printf('<a href="%s">%s</a>', esc_url($author->user_url), esc_html($site_name));
				}else{
					echo 'なし';
				}
			?></dd>
		</dl>
	</div>
	<?php endforeach; ?>

</article>

<?php get_template_part('templates/epub/footer') ?>
