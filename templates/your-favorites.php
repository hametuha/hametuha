<?php
/*
 * ユーザーの投稿したお気に入りのループを表示する
 */
//お気に入りリスト取得
$favorites = get_current_user_favorites();
/* @var $wpdb wpdb*/
global $wpdb;
//コメント件数取得
$total = $wpdb->get_var( 'SELECT FOUND_ROWS()' );


/*
 * コメントなし
 */
if ( ! $total ) : ?>
	<div class="post-content clearfix"><?php the_content(); ?></div>
<?php else : ?>
	<p>現在、<?php echo number_format( $total ); ?>件のお気に入りがあります。</p>
	<?php author_pagination( $total ); ?>
		<?php if ( ! empty( $favorites ) ) : ?>
			<ol class="commentlist favoritelist">
				<?php
				foreach ( $favorites as $fav ) :
					$original_post = get_post( $fav->post_id );
					?>
					<li id="li-fav-<?php echo $fav->ID; ?>">
						<div id="fav-
						<?php
						echo $fav->ID;
						?>
						" class="comment">
							<div class="comment-meta clearfix">
								<div class="comment-author vcard">
									<cite>
										<a href="<?php echo get_permalink( $original_post->ID ); ?>"><?php echo $original_post->post_title; ?></a>
										<strong class="old"> @ <?php echo round( $fav->location, 2 ); ?>%</strong>
									</cite><br />
									<span class="old"><?php echo mysql2date( 'Y-m-d H:i', $fav->updated ); ?></span>
									<span class="edit-link right">
										<a class="delete-fav small-button comment-edit-link" rel="nofollow" id="delete_fav_
										<?php
										echo $fav->ID;
										?>
										" href="#">削除する</a>
										<img class="loader" src="<?php bloginfo( 'template_directory' ); ?>/img/ajax-loader.gif" alt="Loading..." width="16" height="11" />
									</span>
								</div><!-- .comment-author .vcard -->
							</div>

							<div class="comment-content clearfix">
								<?php echo wpautop( $fav->content ); ?>
							</div>

							<div class="reply clearfix">
								<a class="button" href="<?php echo get_permalink( $original_post->ID ); ?>?location=<?php echo $fav->location; ?>">読む</a>
							</div><!-- .reply -->
						</div><!-- #comment-## -->
					</li>
				<?php endforeach; ?>
			</ol>
		<?php else : ?>
			<div class="post-content clearfix">
				<p class="message error">該当するコメントは見つかりませんでした。</p>
			</div>

		<?php endif; ?>
	<?php author_pagination( $total ); ?>
		
<?php endif; ?>
