<?php get_header('narrow'); ?>

<div class="wrap">
	<div <?php post_class('post-meta thread-meta clearfix')?>>
	
		<p class="post-type-bar-narrow">
			<a class="small-button alignright" href="<?php echo get_post_type_archive_link(get_post_type()); ?>">
				破滅派BBSトップへ
			</a> 
			<img src="<?php echo get_stylesheet_directory_uri(); ?>/img/header-logo.png" alt="<?php bloginfo('name'); ?>" width="140" height="50" />
			<span class="description"><?php echo get_post_type_object(get_post_type())->description; ?></span>
		</p>
	</div><!-- //.thread-meta -->

	<div class="archive-meta">
		<h1>破滅派BBS: <?php single_term_title(); ?></h1>
		<div class="desc">
			<?php get_template_part('templates/meta-desc'); ?>
		</div>	
	</div>

	<?php get_template_part('templates/sort-order'); ?>

	<?php wp_pagenavi(); ?>

	
	<table class="works-list thread-list">
		<caption><?php single_term_title() ?>のスレ一覧</caption>
		<tbody>
			<?php if(have_posts()): while(have_posts()): the_post(); ?>
			<tr>
				<th class="thumbnail"><?php echo get_avatar(get_the_author_meta('ID'), 64); ?></th>
				<td class="detail">
					<span class="date mono"><?php the_post_time_diff(true); ?></span>
					<h3 class="title">
						<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					</h3>
					<p class="meta">
						<span>スレ主</span>
						<?php the_author(); ?>
						<span>レス</span>
						<?php echo ($number = get_comments_number());?>件
						<span>最新レス</span>
						<?php
							$latest = get_latest_comment_date();
							echo $latest ? mysql2date('Y/n/j', $latest) : 'なし';
							if(recently_commented()):
						?>
							<img src="<?php echo get_template_directory_uri();?>/img/icon-new-small.png" alt="New" width="16" height="16" />
						<?php endif; ?>
					</p><!-- //.meta -->
					<div class="excerpt">
						<?php the_excerpt(); ?>
					</div><!-- .excerpt -->
				</td>
				<td class="more">
					<a href="<?php the_permalink(); ?>"><img src="<?php echo get_template_directory_uri(); ?>/img/icon-list-table.png" alt="<?php the_title(); ?>" width="64" height="32" /></a>
				</td>
			</tr>
			<?php endwhile;?>
		<?php else: ?>
			<tr>
				<td colspan="3"><p class="message warning">このトピックにはまだスレッドが立っていません。</p></td>
			</tr>
		<?php endif; ?>
		</tbody>
	</table>

	<?php wp_pagenavi(); ?>
	
	<p class="center bottom-link">
		<a href="<?php echo get_post_type_archive_link('thread'); ?>">破滅派BBSトップ</a>
		<?php foreach(get_terms('topic') as $topic){
			printf(' | <a href="%s">%s</a>', get_term_link($topic), $topic->name);
		} ?>
	</p>

</div><!-- //.wrap -->



<?php get_footer('narrow'); ?>