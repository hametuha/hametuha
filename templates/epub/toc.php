<?php
/** @var WP_Post $series */
?>
<?php get_template_part('templates/epub/header') ?>

	<header class="header header--toc">
		<h1 class="title">
			<?php the_title() ?> 目次
		</h1>
	</header>

	<article class="content content--toc clearfix">
		<?= $toc ?>
	</article>

<?php get_template_part('templates/epub/footer') ?>