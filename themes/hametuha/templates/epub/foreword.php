<?php
/** @var WP_Post $series */
/** @var string $preface_title エスケープ済みの見出し。 */
?>
<?php get_template_part( 'templates/epub/header' ); ?>

<div class="header header--preface">
	<h1 class="title">
		<?php echo $preface_title; ?>
	</h1>
</div>

<article class="content content--script content--preface clearfix">

	<?php echo apply_filters( 'the_content', get_post_meta( get_the_ID(), '_preface', true ) ); ?>

</article>

<?php get_template_part( 'templates/epub/footer' ); ?>
