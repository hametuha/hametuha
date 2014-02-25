<?php get_header('meta'); ?>
<body <?php body_class(); ?>>
<?php hametuha_fb_root(); ?>
<div class="single-breadcrumb clearfix">
	<p class="alignleft">
		<?php if(function_exists('bcn_display')) bcn_display(); ?>
	</p>
	<?php if(is_singular()): ?>
	<p class="date right">
		<span class="mono"><?php the_date('Y/n/j'); ?></span>
		<small>（<span class="mono"><?php the_modified_date('Y/n/j'); ?></span> 更新）</small>
	</p>
	<?php endif; ?>
</div>