<?php get_header(); ?>

<?php if(is_singular('faq') || is_tax('faq_cat') || is_post_type_archive('faq')): ?>
	<?php get_template_part('templates/help-center');?>
<?php endif; ?>

<?php if(is_tax('topic')) get_template_part('templates/bbs-header'); ?>

<div class="archive-meta">
	<h1>
		<?php get_template_part('templates/h1'); ?>
		<small class="old"><?php echo number_format_i18n(loop_count()); ?></small>
	</h1>
	<div class="desc">
		<?php get_template_part('templates/meta-desc'); ?>
	</div>
	
	<?php if(have_posts()): ?>
		<?php get_template_part('templates/sort-order'); ?>
	<?php endif; ?>
</div>

<?php
	if(is_singular('series')){
		global $wp_query, $paged, $query_string;
		$old_query = $query_string;
		query_posts('post_type=post&post_status=publish&posts_per_page='.get_option('posts_per_page')."&post_parent=".get_the_ID()."&paged={$paged}");
	}
	if(have_posts()): ?>

<?php wp_pagenavi(); ?>

<ol class="archive-container">
<?php $counter = 0; while(have_posts()): the_post(); $counter++; $even = ($counter % 2 == 0) ? ' even' : ' odd';?>
	<li <?php post_class('archive-list clearfix'.$even); ?>>
		
		<!-- Category -->
		<?php if(get_post_type() == 'post'): ?>
			<span class="category"><?php the_category(', '); ?></span>
		<?php elseif(get_post_type() == 'announcement' && user_can(get_the_author_ID(),'edit_others_posts')): ?>
			<img class="officital-watermark" src="<?php echo get_template_directory_uri();?>/img/single_announcement_title.png" alt="破滅派編集部公式" width="80" height="80" />
		<?php endif; ?>
		
		<!-- Thumbnail -->
		<?php if(false !== array_search(get_post_type(), array('post', 'announcement', 'anpi'))): ?>
			<?php if(has_post_thumbnail()): ?>
				<?php echo get_the_post_thumbnail(get_the_ID(), 'pinky'); ?>
			<?php elseif(get_post_type() != 'post' && has_image_attachment()): ?>
				<?php
					$images = get_children("post_parent=".get_the_ID()."&post_mime_type=image&orderby=menu_order&order=ASC&posts_per_page=1");
					echo wp_get_attachment_image(current($images)->ID, 'pinky', false, array('class' => 'wp-post-image attachment-pinky'));
				?>
			<?php elseif(get_post_type() == 'post'): ?>
				<img alt="" class="wp-post-image" src="<?php echo get_bloginfo('template_directory').'/img/no-photo-pinky-'.get_post_type().".png"; ?>" width="80" height="80" />
			<?php endif; ?>
		<?php endif; ?>
		
		<!-- Title -->
		<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		
		<!-- Author, Date -->
		<p class="author clearfix">
			<?php if(false === array_search(get_post_type(), array('faq', 'info'))): ?>
				<?php echo get_avatar(get_the_author_ID(), 20); ?>
				<?php the_author_posts_link(); ?>
			<?php endif; ?>
			<span class="old">@<?php the_time('Y/m/d'); ?></span>
			<small>（<span class="old"><?php the_modified_date('Y/m/d'); ?></span>更新）</small>
		</p>
		
		<!-- Excerpt -->
		<p class="excerpt">
			<?php echo trim_long_sentence(get_the_excerpt(), 98); ?>
		</p>
		
		<!-- Tag -->
		<?php switch(get_post_type()): case 'post': ?>
			<?php the_all_tags('', false, '<p class="clrB tag-container">', '</p>'); ?>
		<?php break; case 'faq': ?>
			<p class="clrB tag-container"><?php the_terms(get_the_ID(), 'faq_cat', '', ''); ?></p>
		<?php break; endswitch; ?>
		
		<!-- post_type_object -->
		<?php if(is_search()): $post_type = get_post_type_object(get_post_type()); ?>
			<span class="post_type_indicator"><?php echo $post_type->labels->name == '固定ページ' ? 'ページ': $post_type->labels->name ;?></span>
		<?php endif; ?>
	</li>
<?php endwhile;?>

</ol>

<?php wp_pagenavi(); ?>
	
<?php else: ?>

<div class="nocontents-found">
<p>該当するコンテンツがありませんでした。以下の方法をお試しください。</p>
<ul>
	<li>検索ワードを変えてみる</li>
	<li>カテゴリー、タグから探す</li>
	<li>検索ワードの数を減らして、絞り込み検索と組み合せる</li>
</ul>
<p>改善要望などありましたら、<a href="<?php echo home_url('/inquiry/'); ?>">お問い合わせ</a>からお願いいたします。</p>
</div>
	
<?php endif; if(isset($old_query))	 query_posts($old_query); ?>


<p class="center ads"><?php google_ads('narrow'); ?></p>
	
<?php get_footer(); ?>