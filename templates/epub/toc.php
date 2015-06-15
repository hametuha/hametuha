<?php
/** @var WP_Post $series */
?>
<?php get_template_part('templates/epub/header') ?>

	<div class="header header--toc">
		<h1 class="title">
			<?php the_title() ?> 目次
		</h1>
	</div>

	<section class="content content--toc clearfix">
		<?= $toc ?>
	</section>

<?php get_template_part('templates/epub/footer') ?>