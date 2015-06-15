<?php
$series = \Hametuha\Model\Series::get_instance();
get_template_part('parts/bar', 'posttype');
?>


<div class="row meta--series">
	<?php if( has_post_thumbnail() ): ?>
	<div class="col-xs-12 col-sm-3 meta__thumbnail">
		<?php the_post_thumbnail('medium') ?>
	</div>
	<?php endif; ?>

	<div class="col-xs-12<?php if( has_excerpt() ) echo ' col-sm-9'; ?>">

		<!-- title -->
		<div class="page-header">
			<h1 class="post-title post-title--series" itemprop="name">
				<?php the_title(); ?> <small>作品集</small>
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

	</div>
</div>

<?php get_template_part('parts/alert', 'kdp') ?>

<?php get_template_part('parts/share') ?>
