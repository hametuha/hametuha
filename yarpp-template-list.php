<?php
/* @var WP_Post $post */
if (have_posts()):
	?>
<div class="row row--recommend">
	<h3 class="recommend__header">
		<i class="icon-brain"></i><br />
		似たような作品はこちら<br />
		<small>横にスクロールできるよ</small>
	</h3>
	<ul class="recommend__list">
	<?php while (have_posts()) : the_post(); ?>
		<?php get_template_part('parts/loop', 'recommend') ?>
	<?php endwhile; ?>
	</ul>
</div>
<?php endif; ?>
