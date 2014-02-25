<?php
// Do not delete these lines
	if (!empty($_SERVER['SCRIPT_FILENAME']) && 'comments.php' == basename($_SERVER['SCRIPT_FILENAME']))
		die ('Please do not load this page directly. Thanks!');
	
	if ( post_password_required() ) { ?>
		<p class="message error">このページは作成者によってパスワード保護されています。コメントを見るには、パスワードを入力してください。</p>
	<?php
		return;
	}

/**
 * コメント表時関数
 * @param object $comment
 * @param array $args
 * @param int $depth 
 */
function hametuha_commment_display( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	if(get_the_author_ID() == $comment->user_id){
		$comment_class = 'post-author';
	}else{
		$comment_class = 'reader';
	}
	$type = function_exists('feedback_type') ? feedback_type(false, false, false, false, false, false, false, false, false, false, false, false) : 'コメント';
	?>	
	<li <?php comment_class($comment->comment_type); ?> id="li-comment-<?php comment_ID(); ?>">
		<div id="comment-<?php comment_ID(); ?>" class="comment">
			<div class="comment-meta clearfix">
				<div class="comment-author vcard">
					<?php switch($comment->comment_type): 
							case "pingback":
							case "trackback":
								echo '<img alt="pingback" width="32" height="31" src="'.get_bloginfo('template_directory').'/img/icon-comment-pingback.png" />';
								break;
							default:
								$size = ($type == 'コメント') ? 32 : 24;
								echo get_avatar( $comment, $size );
								break;
							endswitch;?>
					<cite>
						<?php echo get_comment_author_link(); ?>
						<small><?php
							if($type == 'コメント'){
								switch($comment->comment_type){
									case "pingback":
									case "trackback":
										echo '外部サイト';
										break;
									default:
										the_author_roles($comment->user_id);
										break;
								}
							}else{
								echo $type;
							}
						?></small>
					</cite><br />
					<span class="old"><?php echo get_comment_date('Y-m-d H:i'); ?></span>
					<?php edit_comment_link( 'このコメントを編集', '<span class="edit-link">', '</span>' ); ?>
				</div><!-- .comment-author .vcard -->

				<?php if ( $comment->comment_approved == '0' ) : ?>
					<em class="comment-awaiting-moderation">このコメントは承認待ちです。</em>
					<br />
				<?php endif; ?>

			</div>

			<div class="comment-content <?php echo $comment_class; ?>"><?php comment_text(); ?></div>
			
			<?php if($type == 'コメント' && $comment->comment_type == 'comment'): ?>
				<div class="reply right">
					<?php comment_reply_link( array_merge( $args, array( 'reply_text' => 'このコメントに返信', 'login_text' => 'ログインして返信', 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
				</div><!-- .reply -->
			<?php endif; ?>
		</div><!-- #comment-## -->

	<?php
}
?>

<!-- You can start editing here. -->

<div id="discussion">
	<h2 id="comments">
		&quot;<?php the_title(); ?>&quot;へのフィードバック
		<span class="comment_count">
			<em class="old"><?php echo number_format_i18n(get_comments_number()); ?></em>件
		</span>
	</h2>
	<?php if ( have_comments() ) : ?>
	
		<?php if(get_comment_pages_count() > 1): ?>
			<div class="link-pages comment-pages">
				<?php paginate_comments_links(array('prev_text' => '&laquo; 前','next_text' => '次 &raquo;')); ?>
			</div>
		<?php endif; ?>
	
		<ol class="commentlist">
		<?php wp_list_comments('callback=hametuha_commment_display');?>
		</ol>

		<?php if(get_comment_pages_count() > 1): ?>
			<div class="link-pages comment-pages">
				<?php paginate_comments_links(array('prev_text' => '&laquo; 前','next_text' => '次 &raquo;')); ?>
			</div>
		<?php endif; ?>
	
	 <?php else : // this is displayed if there are no comments so far ?>

		<?php if ( comments_open() ) : ?>
			<!-- If comments are open, but there are no comments. -->
			<p class="nocomments">
				コメントがありません。
				<?php switch(get_post_type()): 
					case 'faq': ?>
					不明な点があったらコメントをお願いします。
				<?php break; default: ?>
					寂しいので、ぜひコメントを残してください。
				<?php break; endswitch;?>
			</p>
		 <?php else : // comments are closed ?>
			<!-- If comments are closed. -->
			<p class="nocomments">コメントは許可されてません。</p>
		<?php endif; ?>
	<?php endif; ?>
</div>
<!-- // #discussion -->


<?php if ( comments_open()) : ?>
<div id="respond">

	<h2>&quot;<?php the_title(); ?>&quot;へコメントを送信</h2>

	<?php if (is_user_logged_in() || get_post_type() == 'thread'): ?>
		<div id="cancel-comment-reply">
			<small><?php cancel_comment_reply_link('返信をキャンセル') ?></small>
		</div>
		
		<?php
			if(is_user_logged_in()){
				$action = get_option('siteurl').'/wp-comments-post.php';
			}else{
				//スレッドへの匿名投稿
				$action = get_permalink();
				show_thread_error();
			}
		?>
	
		<form action="<?php echo $action; ?>" method="post" id="commentform" class="clearfix">
			<p class="comment-text">
				<textarea placeholder="ここにコメントを記載してください" name="comment" id="comment" cols="58" rows="10" tabindex="4"></textarea>
			</p>

			<p class="comment-allowed-tags">
				<strong>利用できるHTMLタグ: </strong>
				<code class="mono"><?php echo allowed_tags(); ?></code>
			</p>

			<?php if(is_user_logged_in()): ?>
				<p class="comment-as right">
					<?php printf('<a href="%1$s">%2$s</a>としてのコメント', get_option('siteurl') . '/wp-admin/profile.php', $user_identity); ?> 
				</p>
			<?php else: ?>
				<div class="comment-as clearfix">
					<?php hametuha_show_recaptcha();?>
					<p>
						あなたはログインしていないので、匿名でコメントを行います。スパムロボットによるコメント投稿を防ぐため、左のキャプチャを入力してください。
						これによって人間であると判断します。<br />
						ちなみに、<a href="<?php echo wp_login_url(get_permalink().'#respond');?>">ログイン</a>して記名コメントにすると、キャプチャを入力しなくて済みますし、<strong>責任感があるように見えます</strong>。
					</p>
					<?php wp_nonce_field('thread_anonymous_reply', '_anonymous_comment_nonce'); ?>
				</div>
			<?php endif; ?>
			

			<p class="alignleft">
				<input class="button-primary" name="submit" type="submit" id="submit" tabindex="5" value="<?php esc_attr_e('Submit Comment'); ?>" onclick="this.value = '送信中...';" />
				<?php comment_id_fields(); ?>
			</p>
			<?php do_action('comment_form', get_the_ID()); ?>

		</form>
	<?php else : ?>
		<p class="nocomments">
			コメントをするにはユーザー<?php wp_register('', ''); ?>をした上で
			<a href="<?php echo wp_login_url(get_permalink()); ?>">ログイン</a>する必要があります。
		</p>
	
<?php endif; ?>
</div>

<?php endif; // if you delete this the sky will fall on your head
//
global $feedback_champru;
if(isset($feedback_champru) && method_exists($feedback_champru, 'get_avatar')){
	remove_filter('get_avatar', array($feedback_champru, 'get_avatar'));
}
?>