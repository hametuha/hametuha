<?php
/** @var WP_Post $series */
?>
<?php get_template_part('templates/epub/header') ?>

<header class="header header--afterwords">
	<h1 class="title">
		あとがき
	</h1>
</header>

<article class="content content--script content--afterwords clearfix">

	<?php the_content() ?>

</article>

<?php get_template_part('templates/epub/footer') ?>
