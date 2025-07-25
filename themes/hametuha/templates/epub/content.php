<?php
/** @var WP_Post $series */
/** @var int $show_title */
/** @var string $filtered_title */
/** @var int $series_type */
?>
<?php get_template_part( 'templates/epub/header' ); ?>

<?php if ( $show_title ) : ?>
<div class="header header--afterwords">
	<h1 class="title"><?php echo $filtered_title; ?></h1>
	<?php if ( $show_title > 1 && $show_title < 4 ) : ?>
		<p class="header__author"><?php the_author(); ?></p>
	<?php endif; ?>

	<?php if ( 2 < $show_title && has_excerpt() ) : ?>
	<div class="header__lead">
		<?php the_excerpt(); ?>
	</div>
	<?php endif; ?>
</div>
<?php endif; ?>



<article class="content content--script content--afterwords clearfix">

	<?php the_content(); ?>

</article>


<?php if ( $series_type ) : ?>
<footer class="footer footer--content">
	&copy; <?php the_time( 'Y' ); ?> <?php the_author(); ?>
</footer>
<?php endif; ?>

<?php get_template_part( 'templates/epub/footer' ); ?>
