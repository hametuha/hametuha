<?php
/** @var $announcement \Hametuha\ThePost\Announcement */
$announcement = $post->helper;
?>

<?php if( $announcement->is_limited() || $announcement->has_place() ): ?>
<div class="event-detail clearfix">

	<dl class="dl-horizontal">
        <?php if( $announcement->has_place() ): ?>
            <dt>場所</dt>
            <dd>
                <a href="http://www.google.co.jp/maps?q=<?= rawurlencode($post->helper->get_address(false) ) ?>" class="small-button" target="_blank"><?= $post->helper->get_address() ?></a>
            </dd>
        <?php endif; ?>

        <?php if( lwp_has_ticket() ): ?>
            <dt>日時</dt>
            <dd>
                <?php
                    $start = lwp_event_starts('Y/n/j (D) H:i');
                    $end = lwp_event_ends('Y/n/j (D) H:i');
                    echo $start." 〜 ".$end;
                ?>
                <?php if( !lwp_is_event_available() ): ?>
                <span class="label label-danger">
                    このイベントは終了しています。
                </span>
                <?php endif; ?>
            </dd>
            <dt>備考</dt>
            <dd><?= $announcement->notice ?></dd>
        <?php elseif( $announcement->is_limited() ): ?>
            <dt>日時</dt>
            <dd>
                <?= $announcement->get_period();?>
                <?php if( $announcement->is_expired() ): ?>
                <p class="small-message warning">
                    このイベントは終了しています。
                </p>
                <?php endif; ?>
            </dd>
            <dt>備考: </dt>
            <dd><?= $announcement->notice ?></dd>
        <?php endif;?>
	</dl>
	<?php if( $announcement->has_place() ): ?>
		<div id="gmap-announcement" class="row" data-address="<?= $announcement->get_address(true) ?>"></div>
	<?php endif; ?>
</div>
<?php endif; ?>

<?php if( $announcement->is_participating() ):?>
<div class="clearfix event-detail">
	<table class="event-table">
		<tbody>
			<tr>
				<th>募集期間</th>
				<td>
					<?= $announcement->get_participating_period() ?>
					<?php if( $announcement->left_second_to_participate() == 0 ): ?>
						<p class="small-message warning">
							募集は終了しています。
						</p>				
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th>応募条件</th>
				<td>
					<?= $announcement->participating_condition() ?>
				</td>
			</tr>
			<tr>
				<th>参加費用</th>
				<td>
					<?= $announcement->participating_cost() ?>
				</td>
			</tr>
			<tr>
				<th>定員</th>
				<td>
					<?= $announcement->participating_limit() ?>
				</td>
			</tr>
		</tbody>
	</table>
	<div id="participating-form">
		<?php if( $announcement->left_second_to_participate() == 0 ): ?>
			<p class="please-login">
				この募集は締め切りました。ありがとうございました。
			</p>
		<?php else: ?>
			<?php if( !is_user_logged_in() ): ?>
				<p class="please-login">
					応募するには<a href="<?= wp_login_url(get_permalink()); ?>">ログイン</a>する必要があります。
				</p>
			<?php else: ?>
				<form method="post" action="<?= admin_url('admin-ajax.php'); ?>" enctype="multipart/form-data">
					<?php wp_nonce_field('hametuha_participate_'.get_current_user_id()); ?>
					<input type="hidden" name="post_id" value="<?php the_ID(); ?>" />
					<input type="hidden" name="action" value="hametuha_participate" />
					<?php switch(get_post_meta(get_the_ID(), $announcement::COMMIT_TYPE, true)):
								  case 1: // メール応募 ?>
						<h4>メールで応募</h4>
						<p>
							<label for="mail_body">メッセージ:</label><br />
							<textarea rows="5" name="mail_body" id="mail_body"></textarea>
						</p>
						<?php if(get_post_meta(get_the_ID(), $announcement::COMMIT_FILE, true)): ?>
							<p>
								<input type="hidden" name="MAX_FILE_SIZE" value="2097152" />
								<input type="file" name="file" />
							</p>
							<p class="description">ファイルサイズは<strong>2MB</strong>まで、<strong>ファイル名は半角英数</strong>にしてください。</p>
						<?php endif; ?>
						<p class="submit center">
							<input type="submit" class="button" value="応募する" />
						</p>
					<?php break; case 2: //投稿 ?>
						<?php if( !current_user_can('edit_posts') ): ?>
							<p class="please-login">
								応募するには同人になる必要があります。くわしくは<a href="<?php echo home_url('/faq/how-to-post/');?>">どうやったら投稿できますか</a>をご覧下さい。
							</p>
						<?php else: ?>
							<?php $user_posts = $announcement->get_committed_posts(get_current_user_id()); if( empty($user_posts) ): ?>
							<p class="please-login">
								応募条件に当てはまる投稿をがまだありません。&nbsp;
								<strong><?php echo get_post_type_object(get_post_meta(get_the_ID(), Hametuha_Announcement_Helper::COMMIT_POST_TYPE, true))->labels->name;?></strong>に
								<?php $categories = $announcement->get_required_taxonomies_to_commit(); if( !empty($categories)): ?>
									次のいずれかのカテゴリー<strong>（<?php $cats = array(); foreach($categories as $cat): 
										$cats[] = $cat->name;
									endforeach; echo implode('、', $cats);?>）</strong>で
								<?php endif; ?>
								投稿してください。
							</p>	
							<p class="center">
								<a class="button-primary" href="<?= admin_url('post-new.php?post_type='.$announcement->commit_post_type); ?>">投稿する</a>
							</p>
							<?php else: ?>
							<p>以下の投稿が応募条件に当てはまっています。</p>
							<ol>
								<?php foreach($user_posts as $p): ?>
									<li>
										<a href="<?= get_permalink($p->ID); ?>"><?= esc_html($p->post_title); ?></a> &nbsp;
                                        <?php if( $p->post_author == get_current_user_id() ): ?>
		    								<a class="small-button" href="<?= esc_attr(admin_url('post.php?post='.$p->ID.'&action=edit'));?>">編集</a>
                                        <?php endif; ?>
									</li>
								<?php endforeach; ?>
							</ol>
							<?php endif; ?>
						<?php endif; ?>
					<?php break; case 3: //参加表明 ?>
						
					<?php break; endswitch; ?>
				</form>
			<?php endif; ?>
		<?php endif; ?>
	</div>

	<?php if(isset($_GET['error-message'])): ?>
		<p class="message error clrB">
			<?php switch($_GET['error-message']){
				case 0:
					echo 'この募集には応募できません。';
					break;
				case 1:
					echo '募集期限切れです。';
					break;
				case 2:
					echo '定員オーバーのため参加できません。';
					break;
				case 3:
					echo 'すでに参加しています。';
					break;
				case 4:
					echo 'ファイルが添付されていません。';
					break;
				case 5:
					echo 'ファイルの保存に失敗しました。やりなおしてください。';
					break;
				case 6:
					echo 'メールの送信に失敗しました。';
					break;
			}?>
		</p>
	<?php endif; ?>

	<?php if(isset($_GET['message'])): ?>
		<p class="message success clrB">
			<?php switch($_GET['message']){
				case 1:
					echo 'ご応募を受け付けました。ありがとうございました。';
					break;
			}?>
		</p>
	<?php endif; ?>

</div>
<?php endif; ?>


<?php if( 2 == $announcement->commit_type ): $committed_posts = $announcement->get_committed_posts(); ?>
    <?php if(!empty($committed_posts)): ; ?>
    <h2>参加している投稿 <small><?php echo number_format_i18n(count($commited_posts));?>件</small></h2>
    <ol class="participating-posts">
        <?php $counter = 0; foreach($committed_posts as $p): ?>
            <li class="<?php echo ($counter % 2 == 0) ? 'even' : 'odd';?>">
                <?php echo get_avatar($p->post_author, 20); ?>
                <?php echo get_the_author_meta('ID', $p->post_author);?>:
                <a href="<?php echo get_permalink($p->ID); ?>"><?= $p->post_title; ?></a>
                <small>@<?php echo mysql2date("Y/m/d", $p->post_date); ?></small>
            </li>
        <?php $counter++; endforeach; ?>
    </ol>
    <?php endif; ?>
<?php endif; ?>
