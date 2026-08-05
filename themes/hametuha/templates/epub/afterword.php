<?php
/** @var WP_Post $series */
/** @var string $afterword_title エスケープ済みの見出し。 */
?>
<?php get_template_part( 'templates/epub/header' ); ?>

<div class="header header--afterwords">
	<h1 class="title">
		<?php echo $afterword_title; ?>
	</h1>
</div>

<article class="content content--script content--afterwords clearfix" epub:type="afterword">

	<?php the_content(); ?>

</article>

<?php get_template_part( 'templates/epub/footer' ); ?>
