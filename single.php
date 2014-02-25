<?php get_header(); ?>


<?php if(have_posts()): while(have_posts()): the_post(); ?>
<div <?php post_class('post-meta')?>>
	<?php if(get_post_type() == 'faq'): ?>
		<?php get_template_part('templates/help-center'); ?>
	<?php else: ?>
		<p class="post-type-bar clearfix">
			<?php $post_object = get_post_type_object(get_post_type());?>
			<a class="small-button alignright" href="<?php echo get_post_type_archive_link(get_post_type()); ?>">
				<?php echo $post_object->labels->name; ?>トップへ
			</a> 
			<?php echo $post_object->labels->name; ?>
		</p>
	<?php endif; ?>
	<?php if(has_post_thumbnail()): ?>
		<h1 class="thumbnail">
			<?php the_post_thumbnail('large'); ?>
			<?php if(is_public_announcement()): ?>
				<img class="official-watermark" src="<?php echo get_template_directory_uri();?>/img/single_announcement_title.png" alt="破滅派編集部公式" width="80" height="80" />
			<?php endif; ?>
		</h1>
	<?php else: ?>
		<h1 class="mincho">
			<?php if(is_public_announcement()): ?>
				<img class="official-watermark" src="<?php echo get_template_directory_uri();?>/img/single_announcement_title.png" alt="破滅派編集部公式" width="80" height="80" />
			<?php endif; ?>
			<?php the_title(); ?>
		</h1>
	<?php endif; ?>
	<div class="metadata clearfix">
		<p class="genre tag-container">
			<?php switch(get_post_type()){
				case 'faq':
					the_terms(get_the_ID(), 'faq_cat', '', ', ');
					break;
				case 'anpi':
					the_terms(get_the_ID(), 'anpi_cat', '', ', ');
					break;
			}?>
		</p>
		<p class="date right">
			<?php echo get_avatar(get_the_author_ID(), '20'); ?>
			<span class="author"><?php the_author(); ?></span>
			<span class="old">@<?php the_date('Y/m/d'); ?></span>
			<small>（<span class="old"><?php the_modified_date('Y/m/d'); ?></span>更新）</small>
		</p>
		<?php if(!empty($post->post_excerpt)): ?>
		<div class="excerpt">
			<?php the_excerpt(); ?>
		</div><!-- //.excerpt -->
		<?php endif; ?>
	</div><!-- //.metadta -->
</div>

	

<?php get_template_part('templates/announcement'); ?>


<?php get_template_part('templates/table', 'ticket'); ?>


<div class="post-content clearfix">
	<?php the_content(); ?>
	<?php wp_link_pages(array('before' => '<p class="link-pages clrB">ページ: ', 'after' => '</p>', 'link_before' => '<span>', 'link_after' => '</span>')); ?>
</div><!-- //.post-content -->


<?php get_template_part('templates/table', 'ticket'); ?>
	



<?php hametuha_share(get_the_title(), get_permalink()) ; ?>

<div class="more">
	<?php switch(get_post_type()){
		case "faq":
		case "announcement":
			comments_template();
			break;
		case 'anpi':
			?>
			<div class="next-previous center">
				<?php previous_post_link('%link', '&laquo; %title'); ?>
				<span class="divider">｜</span>
				<?php next_post_link('%link', '%title &raquo;'); ?>
			</div>
			<?php
			comments_template(); 
			break;
	} ?>
</div>

<?php endwhile; endif; ?>

<?php get_footer(); ?>