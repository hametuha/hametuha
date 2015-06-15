<?php
/** @var WP_Post $series */
?>
<?php get_template_part('templates/epub/header') ?>

<div class="header header--preface">
	<h1 class="title">
		はじめに
	</h1>
</div>

<article class="content content--script content--preface clearfix">

	<?= apply_filters('the_content', get_post_meta(get_the_ID(), '_preface', true)) ?>

</article>

<?php get_template_part('templates/epub/footer') ?>
