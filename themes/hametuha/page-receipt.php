<?php
/**
 * Template Name: 印刷用ページ
 *
 */
get_header( 'meta' );
the_post();
?>
<main class="paper-body">
	<div class="paper-inner container">
		<header class="paper-header">
			<h1><?php single_post_title(); ?></h1>
		</header>
		<article class="paper-content">
			<?php the_content(); ?>
		</article>
	</div>
</main>

<?php wp_footer(); ?>
</body>
</html>
