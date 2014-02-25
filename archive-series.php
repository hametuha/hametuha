<?php get_header('narrow'); ?>

<div class="wrap">
	<div <?php post_class('post-meta post-meta-single clearfix')?>>
		<h1 class="mincho">
			<span class="series"><a href="<?php echo home_url('/', 'http'); ?>">トップへ戻る</a></span>
			破滅派同人による作品集
		</h1>
		<p class="genre">
			<span class="category">全<?php global $wp_query; echo number_format_i18n($wp_query->found_posts); ?>作品集</span>
		</p>

		<img class="attachment-post-thumbnail" width="300" height="400" alt="<?php the_title(); ?>" src="<?php echo get_template_directory_uri(); ?>/img/covers/default-300x400.jpg" />

		<div class="desc clearfix clrB">
			<?php echo wpautop(get_post_type_object('series')->description); ?>
		</div>

	</div><!-- //.post-meta-single -->

	<?php /*
	<div class="post-content single-post-content mincho clearfix">
		<?php 
			global $post;
			if(!empty($post->post_content)){
				the_content();
			}else{
				'なにも書いてない';
			}
			//TODO: 作品集をePub書き出しする場合にどうするか検討
			wp_link_pages(array('before' => '<p class="link-pages clrB">ページ: ', 'after' => '</p>', 'link_before' => '<span>', 'link_after' => '</span>'));
		?>
	</div><!-- //.single-post-content -->
	*/ 
	wp_pagenavi();
	?>

	<table class="works-list serires-list">
		<caption>作品集一覧</caption>
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
						<span>編集者</span>
						<?php the_author(); ?>
						<span>作品数</span>
						<?php echo number_format_i18n(get_post_children_count()); ?>
						<span>文字数</span>
						<?php the_post_length('', '', '計測不能');?>
					</p>
					<div class="excerpt">
						<?php the_excerpt(); ?>
					</div><!-- .excerpt -->
				</td>
				<td class="more">
					<a href="<?php the_permalink(); ?>"><img src="<?php echo get_template_directory_uri(); ?>/img/icon-list-table.png" alt="<?php the_title(); ?>" width="64" height="32" /></a>
				</td>
			</tr>
			<?php endwhile; endif; ?>
		</tbody>
	</table><!-- //.works-list -->

	<?php wp_pagenavi(); ?>

	<p id="single-post-footernote">
		&copy; 2007 Hametuha
	</p>
</div><!-- //.wrap -->


<?php wp_footer(); ?>
</body>
</html>