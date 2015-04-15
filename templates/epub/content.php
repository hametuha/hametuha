<?php
/** @var WP_Post $series */
/** @var bool $show_title */
?>
<?php get_template_part('templates/epub/header') ?>

<?php if( $show_title ): ?>
<header class="header header--afterwords">
	<h1 class="title"><?php the_title() ?></h1>
	<?php if( $show_title > 1 ): ?>
		<p class="header__author"><?php the_author() ?></p>
	<?php endif; ?>
</header>
<?php endif; ?>

<article class="content content--script content--afterwords clearfix">

	<?php the_content() ?>

</article>

<?php get_template_part('templates/epub/footer') ?>
