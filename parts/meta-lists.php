<?php get_template_part('parts/bar', 'posttype') ?>

<?php get_template_part('parts/meta', 'thumbnail') ?>


<!-- title -->
<div class="page-header">
	<h1 class="post-title" itemprop="name">
		<?php the_title(); ?>
		<?php if( is_recommended() ):?>
			<span class="label label-danger">オススメ</span>
		<?php else: ?>
			<small>リスト</small>
		<?php endif; ?>

	</h1>
</div>

<!-- Meta data -->
<div <?php post_class('post-meta')?>>
	<?php get_template_part('parts/meta', 'single'); ?>
</div><!-- //.post-meta -->

<?php if( has_excerpt() ): ?>
	<div class="excerpt">
		<?php the_excerpt(); ?>
	</div><!-- //.excerpt -->
<?php endif; ?>
