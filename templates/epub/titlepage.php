<?php
/** @var WP_Post $series */
?>
<?php get_template_part('templates/epub/header') ?>

<section class="titlepage" epub:type="titlepage">

	<!-- 表紙なし -->
	<h1 class="titlepage__title">
		<span><?php the_title() ?></span>
		<?php if( ($subtitle = get_post_meta(get_the_ID(), 'subtitle', true)) ): ?>
		<small class="subtitle"><?= esc_html($subtitle) ?></small>
		<?php endif; ?>
		<small><?php the_author() ?></small>
		<small class="publisher-credit">破滅派</small>
	</h1>

</section>

<?php get_template_part('templates/epub/footer') ?>
