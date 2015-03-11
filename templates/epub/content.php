<?php
/** @var WP_Post $series */
/** @var bool $drop_title */
?>
<?php get_template_part('templates/epub/header') ?>

<?php if( !$drop_title ): ?>
<header class="header header--afterwords">
	<h1 class="title"><?php the_title() ?></h1>
</header>
<?php endif; ?>

<article class="content content--script content--afterwords clearfix">

	<?php the_content() ?>

</article>

<?php get_template_part('templates/epub/footer') ?>
