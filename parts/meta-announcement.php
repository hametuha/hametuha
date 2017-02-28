<?php
/** @var $announcement \Hametuha\ThePost\Announcement */
$announcement = $post->helper;


get_template_part( 'parts/event', 'address' )

?>

<?php if ( $announcement->is_participating() ) :
	$rest_time_for_limit = $announcement->left_second_to_participate();
	// enqueue

	?>

	<table class="table event-detail-table">
		<caption>イベント参加の詳細</caption>
		<tr>
			<th>募集期間</th>
			<td>
				<?= $announcement->get_participating_period() ?>
				<?php if ( 0 === $rest_time_for_limit ) : ?>
					<span class="label label-danger">終了</span>
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
	</table>

	<div class="event-detail clearfix">
	<?php if ( 0 < $rest_time_for_limit ) : ?>
		<h2 class="event-detail-title">参加フォーム</h2>
		<?php if ( current_user_can( 'read' ) ) : ?>
		<form class="event-user-form">
			<div class="form-group">
				<label for="participant_status">
					<input type="checkbox" name="participant_status" id="participant_status"
						   value="1" <?php checked( $announcement->in_list( get_current_user_id() ) ) ?> />
					このイベントに参加する
				</label>
			</div>
			<div class="form-group">
				<label for="participant_text">コメント</label>
				<textarea id="" name=""><?php
					echo esc_textarea( $announcement->guest_commment( get_current_user_id() ) );
				?></textarea>
			</div>
		</form>
			<?php if ( current_user_can( 'edit_others_posts' ) ) : ?>
				編集者専用フォーム
			<?php endif; ?>
		<?php else : ?>
		<div class="alert alert-warning event-detail-alert">
			<p class="text-center">
				イベントに参加するには<a href="<?= wp_login_url( get_permalink() ) ?>" class="alert-link">ログイン</a>する必要があります。
			</p>
			<p class="text-center">
				<a class="btn btn-success btn-lg" href="<?= wp_login_url( get_permalink() ) ?>">ログインして参加</a>
			</p>
		</div>
		<?php endif; ?>
	<?php else : ?>
		<div class="alert alert-warning event-detail-over">
			このイベントはすでに申し込み期限を過ぎています。
		</div>
	<?php endif; ?>
	</div>
<?php endif; ?>

<?php if ( 2 == $announcement->commit_type ) : $committed_posts = $announcement->get_committed_posts(); ?>
	<?php if ( ! empty( $committed_posts ) ) : ?>
		<h2>参加している投稿
			<small><?php echo number_format_i18n( count( $committed_posts ) ); ?>件</small>
		</h2>
		<ol class="participating-posts">
			<?php $counter = 0;
			foreach ( $committed_posts as $p ) : ?>
				<li class="<?php echo ( 0 == $counter % 2 ) ? 'even' : 'odd'; ?>">
					<?php echo get_avatar( $p->post_author, 20 ); ?>
					<?php echo get_the_author_meta( 'ID', $p->post_author ); ?>:
					<a href="<?php echo get_permalink( $p->ID ); ?>"><?= $p->post_title; ?></a>
					<small>@<?php echo mysql2date( "Y/m/d", $p->post_date ); ?></small>
				</li>
				<?php $counter ++; endforeach; ?>
		</ol>
	<?php endif; ?>
<?php endif; ?>
