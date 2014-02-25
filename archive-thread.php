<?php get_header('narrow'); ?>

<div class="wrap">
	<div class="post-meta">
		<p class="post-type-bar-narrow">
			<a class="small-button alignright" href="<?php bloginfo('url'); ?>">
				破滅派トップへ
			</a> 
			<img src="<?php echo get_stylesheet_directory_uri(); ?>/img/header-logo.png" alt="<?php bloginfo('name'); ?>" width="140" height="50" />
			<span class="description"><?php echo get_post_type_object(get_post_type())->description; ?></span>
		</p>
		<h1 class="thumbnail center"><?php get_template_part('templates/bbs-header'); ?></h1>
	</div>

	<p class="center">
		<a class="button-primary" href="#thread-add">スレッドを追加する</a>
	</p>


	<?php $topics = get_terms('topic', array('hide_empty' => false)); ?>


	<?php foreach($topics as $topic): ?>
	<div class="topic-container">
		<h2 class="clearfix">
			<?php echo esc_html($topic->name); ?>
			<a class="small-button alignright" href="<?php echo get_term_link($topic);?>">一覧へ</a>
			<small>［<?php echo number_format($topic->count); ?>スレッド］</small>
		</h2>
		<div class="description">
			<?php echo wpautop($topic->description); ?>
		</div>
		<?php $query = new WP_Query("post_type=thread&posts_per_page=10&topic={$topic->slug}"); if($query->have_posts()): ?>
		<ol>
			<?php while($query->have_posts()): $query->the_post(); ?>
			<li>
				<?php echo get_avatar(get_the_author_meta('ID'), 24); ?>
				<a href="<?php the_permalink(); ?>">
					<?php the_title(); ?>(<span class="mono"><?php echo get_comments_number();?></span>)
				</a>
				<?php if(recently_commented() || is_new_post()): ?>
					<img src="<?php echo get_template_directory_uri();?>/img/icon-new-small.png" alt="New" width="16" height="16" />
				<?php endif; ?>
				<small class="date"><?php the_post_time_diff(true); ?></small>
			</li>
			<?php endwhile; wp_reset_query();?>
		</ol>
		<?php else: ?>
			<p class="message warning">このトピックにはまだスレッドが立っていません。</p>
		<?php endif; ?>
	</div><!-- //.topic-container -->

	<?php endforeach; ?>

	<div class="post-content">
		<form id="thread-add" method="post" action="<?php echo get_post_type_archive_link('thread'); ?>#thread-add">
			<?php wp_nonce_field('hametuha_add_thread');?>
			<h2>スレッド作成フォーム</h2>
			<?php if(!is_user_logged_in()): ?>
			<p class="message notice">匿名のままスレッドを作成するか、<a href="<?php echo wp_login_url(get_post_type_archive_link('thread'));?>">ログイン</a>してください。</p>
			<?php endif; ?>
			<?php show_thread_error();?>
			<table class="form-table">
				<tbody>
					<tr>
						<th>
							<label for="thread_title">スレッドタイトル</label>
							<span class="required">必須</span>
						</th>
						<td>
							<input type="text" class="regular-text<?php if(get_thread_error('thread_title')) echo ' error';?>" name="thread_title" id="thread_title" value="<?php if(isset($_REQUEST['thread_title'])) echo esc_attr($_REQUEST['thread_title']);?>" /><br />
							<span class="description">30文字を超える部分は切り捨てられます。</span>
						</td>
					</tr>
					<tr>
						<th><label for="thread_content">詳細</label></th>
						<td>
							<textarea rows="8" name="thread_content" id="thread_content"><?php if(isset($_REQUEST['thread_content'])) echo esc_html($_REQUEST['thread_content']);?></textarea><br />
							<span class="description">HTMLタグは使えません。自動で除去されます。URLは自動でリンクします。</span>
						</td>
					</tr>
					<tr>
						<th>
							<label for="topic_id">トピック</label>
							<span class="required">必須</span>
						</th>
						<td>
							<select name="topic_id" id="topic_id"<?php if(get_thread_error('topic_id')) echo ' class="error"';?>>
								<option value="0"<?php if(!isset($_REQUEST['topic_id'])) echo ' selected="selected"'?>>選択してください</option>
								<?php foreach($topics as $t): ?>
								<option value="<?php echo $t->term_id; ?>"<?php if(isset($_REQUEST['topic_id']) && $_REQUEST['topic_id'] == $t->term_id) echo ' selected="selected"'?>><?php echo esc_html($t->name); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<?php if(is_user_logged_in()): ?>
					<tr>
						<th><label>スレッド記名</label></th>
						<td>
							<label>
								<input type="checkbox" name="anonymous" value="1"<?php if(isset($_REQUEST['anonymous']) && $_REQUEST['anonymous']) echo ' checked="checked"';?> />
								匿名ユーザーとしてスレッドを作成
							</label><br />
							<span class="description">匿名ユーザーとしてスレッドを作成した場合、編集はできません。</span>
						</td>
					</tr>
					<?php else: ?>
					<tr>
						<th>
							<label for="recaptcha_response_field">スパム確認</label>
							<span class="required">必須</span>
						</th>
						<td>
							<?php hametuha_show_recaptcha();?>
							<?php if(($message = get_thread_error('recaptcha'))): ?>
								<p class="small-message error"><?php echo $message; ?></p>
							<?php endif; ?>
							<span class="description">
								スパムロボットによる投稿を防止するため、ご協力をお願いします。
								<?php help_tip('2つの単語が表示されていますが、簡単な方を間違えなければ「スパムではない」と認証されます。難しい方の単語はGoogleの電子書籍化プロジェクトで機械が読みとれなかった単語だそうです。'); ?>
							</span>

						</td>
					</tr>
					<?php endif; ?>
				</tbody>
			</table>
			<p class="submit center">
				<input type="submit" class="button-primary" value="この内容でスレッドを作成" onclick="this.value='送信中...'; " />
			</p>
		</form>
	</div>
	<!-- //.post-content -->
	
</div><!-- //.wrap -->



<?php get_footer('narrow'); ?>