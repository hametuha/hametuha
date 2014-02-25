<?php
/*
 * ユーザーのレビューを表示する
 */
//レビューリスト取得
$reviews = get_current_user_reviews();
/* @var $wpdb wpdb*/
global $wpdb;
//コメント件数取得
$total = $wpdb->get_var("SELECT FOUND_ROWS()");


/*
 * レビューなし
 */
if(!$total): ?>
	<div class="post-content clearfix"><?php the_content(); ?></div>
<?php else: ?>
	<p>現在、<?php echo number_format($total); ?>件のレビューをつけています。</p>
	<?php author_pagination($total); ?>
		<?php if(!empty($reviews)): ?>
			<ol class="commentlist favoritelist">
				<?php $counter = 0; foreach($reviews as $review):$original_post = get_post($review->post_id); $counter++;?>
					<li id="li-fav-<?php echo $review->ID; ?>" class="depth-1 <?php echo $counter % 2 == 0 ? 'thread-even' : 'thread-odd'; ?>">
						<div id="fav-<?php echo $review->ID; ; ?>" class="comment">
							<div class="comment-meta clearfix">
								<div class="comment-author vcard">
									<cite>
										<a href="<?php echo get_permalink($original_post->ID); ?>"><?php echo $original_post->post_title; ?></a>
									</cite><br />
									<span class="old"><?php echo mysql2date('Y-m-d H:i', $review->updated); ?></span>
									<span class="edit-link right">
										<a class="edit-feedback small-button comment-edit-link" rel="nofollow" id="edit_feedback_<?php echo $review->ID; ; ?>" href="<?php echo get_feedback_page_url(); ?>&amp;post_id=<?php echo $review->post_id; ?>">修正</a>
									</span>
								</div><!-- .comment-author .vcard -->
							</div>

							<div class="comment-content clearfix">
								<div class="ranker">
									<?php for($i = 1; $i <= 5; $i++): ?>
									<a class="qtip<?php if($review->rank >= $i ) echo ' active'; ?>" href="#" title="星<?php echo $i; ?>つ"><?php echo $i; ?></a>
									<?php endfor;?>
								</div>
								<?php the_review_table(get_current_user_id(), $review->post_id);?>
							</div>

						</div><!-- #comment-## -->
					</li>
				<?php endforeach; ?>
			</ol>
		<?php else: ?>
			<div class="post-content clearfix">
				<p class="message error">該当するコメントは見つかりませんでした。</p>
			</div>

		<?php endif; ?>
	<?php author_pagination($total); ?>
		
<?php endif; ?>
