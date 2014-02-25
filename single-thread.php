<?php get_header('narrow'); ?>


<div class="wrap">
<?php if(have_posts()): while(have_posts()): the_post(); ?>
	<div <?php post_class('post-meta thread-meta clearfix')?>>
		<p class="post-type-bar-narrow">
			<a class="small-button alignright" href="<?php echo get_post_type_archive_link(get_post_type()); ?>">
				破滅派BBSトップへ
			</a> 
			<img src="<?php echo get_stylesheet_directory_uri(); ?>/img/header-logo.png" alt="<?php bloginfo('name'); ?>" width="140" height="50" />
			<span class="description"><?php echo get_post_type_object(get_post_type())->description; ?></span>
		</p>
		<div class="thread-info">
			<?php echo get_avatar(get_the_author_ID(), '80'); ?>
			<p class="author">
				<small><?php the_author_roles(get_the_author_meta('ID')); ?></small><br />
				<?php the_author(); ?>
			</p>
			<table>
				<tbody>
					<tr>
						<th>スレ立て</th>
						<td class="mono"><?php echo number_format_i18n(get_author_thread_count(get_the_author_meta('ID')));?></td>
					</tr>
					<tr>
						<th>コメント</th>
						<td class="mono"><?php echo number_format_i18n(get_author_response_count(get_the_author_meta('ID')));?></td>
					</tr>
				</tbody>
			</table>
			<?php if(user_can(get_the_author_meta('ID'), 'edit_posts')): ?>
			<p class="center">
				<a class="small-button" href="<?php echo get_author_posts_url(get_the_author_meta('ID'));?>">
					投稿一覧
				</a>
			</p>
			<?php endif; ?>
		</div><!-- //.thread-info -->

		<div class="topic tag-container clearfix">
			<?php the_terms(get_the_ID(), 'topic', '', ', '); ?>
			<p class="alignright mono">
				<span><?php the_post_time_diff(); ?></span>
			</p>
		</div>

		<h1 class="mincho"><?php the_title(); ?></h1>

		<div class="thread-body">
			<?php if(isset($_GET['action']) && $_GET['action'] == 'edit' && user_can_edit_post(get_current_user_id(), get_the_ID())): ?>
				<?php show_thread_error();?>
				<?php if(isset($_REQUEST['_wpnonce']) && !get_thread_error()): ?>
					<p class="message success">スレッドを更新しました。</p>
				<?php endif;?>
				<form method="post" action="<?php the_permalink(); ?>?action=edit">
					<?php wp_nonce_field('hametuha_thread_edit'); ?>
					<p>
						<label for="thread_title">タイトル</label><br />
						<input type="text" style="width: 90%" class="regular-text" name="thread_title" id="thread_title" value="<?php echo esc_attr(get_the_title()); ?>" />
					</p>
					<p>
						<label for="thread_content">詳細</label><br />
						<textarea rows="8" style="width: 90%" name="thread_content" id="thread_content"><?php echo strip_tags(get_the_content()); ?></textarea>
					</p>
					<p>
						<label for="topic_id">トピック</label><br />
						<?php
							$topics = get_the_terms(get_the_ID(), 'topic');
							$topic = current($topics); 
							wp_dropdown_categories("taxonomy=topic&name=topic_id&selected={$topic->term_id}&hide_empty=0");
						?>
					</p>
					<p class="submit">
						<input type="submit" value="スレッドを更新" class="button-primary" onclick="this.value = '送信中...';" />
					</p>
				</form>
				<p class="clearfix">
					<a class="button alignleft" href="<?php the_permalink(); ?>">編集を終了</a>
					<a class="button alignright" href="<?php echo wp_nonce_url(get_permalink().'?action=delete', 'hametuha_thread_delete');?>" onclick="if(!confirm('本当にこのスレッドを削除してよろしいですか？')) return false;">スレッドを削除</a>
				</p>
			<?php else: ?>
				<?php if(user_can_edit_post(get_current_user_id(), get_the_ID())): ?>
					<p class="right">
						<a class="button" href="<?php the_permalink(); ?>?action=edit">このスレッドを編集</a>
					</p>
				<?php endif;  ?>
				<?php echo wpautop(hametuha_auto_link(strip_tags(get_the_content()))); ?>
				<?php hametuha_share(get_the_title(), get_permalink(), '', true) ; ?>
			<?php endif; ?>
		</div><!-- //.thread-body -->

		<div class="next-previous center clrB">
			<?php previous_post_link('%link', '&laquo; %title'); ?>
			<span class="divider">｜</span>
			<?php next_post_link('%link', '%title &raquo;'); ?>
		</div>
		
	</div>

	<div class="more">
		<?php comments_template(); ?>
	</div>

	<p class="center bottom-link">
		<a href="<?php echo get_post_type_archive_link('thread'); ?>">破滅派BBSトップ</a>
		<?php foreach(get_terms('topic') as $topic){
			printf(' | <a href="%s">%s</a>', get_term_link($topic), $topic->name);
		} ?>
	</p>
	
<?php endwhile; endif; ?>

</div><!-- //.wrap -->

<?php get_footer('narrow'); ?>