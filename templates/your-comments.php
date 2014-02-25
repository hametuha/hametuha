<?php
/*
 * ユーザーの投稿したコメントループを表示する
 */
//コメントリスト取得
$comments = get_current_user_comments();
/* @var $wpdb wpdb*/
global $wpdb;
//コメント件数取得
$total = $wpdb->get_var("SELECT FOUND_ROWS()");


/*
 * コメントなし
 */
if(!$total): ?>
	<div class="post-content clearfix"><?php the_content(); ?></div>
<?php else: ?>
	
	<p>現在、<?php echo number_format($total); ?>件のコメントを書いています。</p>
	
	<?php author_pagination($total); ?>
	
	<div id="discussion">
		
		<?php if(!empty($comments)): ?>
			<ol class="commentlist">
				<?php foreach($comments as $comment): $original_post = get_post($comment->comment_post_ID); $comment_class = $original_post->post_author == get_current_user_id() ? 'post-author' : '';?>
					<li <?php comment_class($comment->comment_type); ?> id="li-comment-<?php comment_ID(); ?>">
						<div id="comment-<?php comment_ID(); ?>" class="comment">
							<div class="comment-meta clearfix">
								<div class="comment-author vcard">
									<cite>
										<?php echo comment_author(); echo 'さん &gt; ';  ?>
										<a href="<?php echo get_permalink($original_post->ID); ?>"><?php echo $original_post->post_title; ?></a>
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

							<div class="reply clearfix">
								<?php if($comment->comment_parent > 0):?>
									<a class="small-button alignright" href="<?php echo get_permalink($original_post->ID);?>#li-comment-<?php echo $comment->comment_parent; ?>">
										※<?php echo $comment->comment_parent; ?>への返信
									</a>
								<?php endif; ?>
								<a class="button" href="<?php echo get_permalink($original_post->ID); ?>#discussion">スレッドを見る</a>
							</div><!-- .reply -->
						</div><!-- #comment-## -->
					</li>
				<?php endforeach; ?>
			</ol>
		
		
		<?php else: ?>
			<div class="post-content clearfix">
				<p class="message error">該当するコメントは見つかりませんでした。</p>
			</div>
		<?php endif; ?>
	</div>
	
	<?php author_pagination($total); ?>
<?php endif; ?>