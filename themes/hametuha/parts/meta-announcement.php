<?php
/** @var $announcement \Hametuha\ThePost\Announcement */
$announcement = $post->helper;


get_template_part( 'parts/event', 'address' )

?>

<?php
if ( $announcement->can_participate() ) :
	$rest_time_for_limit = $announcement->left_second_to_participate();
	?>
	<div class="event-participate">

		<table class="table event-detail-table">
			<caption>イベント参加の詳細</caption>
			<tr>
				<th>募集期間</th>
				<td>
					<?php echo $announcement->get_participating_period(); ?>
					<?php if ( 0 === $rest_time_for_limit ) : ?>
						<span class="label label-danger">終了</span>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th>応募条件</th>
				<td>
					<?php echo $announcement->participating_condition(); ?>
				</td>
			</tr>
			<tr>
				<th>参加費用</th>
				<td>
					<?php echo $announcement->participating_cost(); ?>
				</td>
			</tr>
			<tr>
				<th>定員</th>
				<td>
					<div>
						<?php
						printf(
							// translators: %1$d is number of current participants, %2$d is limit number.
							esc_html__( '%1$d / %2$d 名', 'hametuha' ),
							$announcement->participating_count(),
							$announcement->participating_limit( false )
						);
						?>
						<?php if ( current_user_can( 'read' ) ) : ?>
							<div class="event-detail-list">
								<?php foreach ( $announcement->get_participants() as $participant ) : ?>
								<div class="event-detail-user" uib-tooltip="<?php echo esc_attr( $participant['text'] ); ?>" tooltip-placement="top">
									<?php echo get_avatar( $participant['id'], 96, '', $participant['name'] ); ?>
									<strong>
										<a href="<?php echo esc_url( $participant['url'] ); ?>">
											<?php echo esc_html( $participant['name'] ); ?>
										</a>
									</strong>
								</div>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</div>
				</td>
			</tr>
		</table>
		<div class="event-detail clearfix">
			<?php if ( 0 < $rest_time_for_limit ) : ?>
				<?php
				if ( current_user_can( 'read' ) ) :
					if ( $announcement->participating_limit( false ) <= $announcement->participating_count() ) :
						?>
						<div class="alert alert-warning event-detail-alert">
							<?php esc_html_e( 'このイベントはすでに定員を超過しています。', 'hametuha ' ); ?>
						</div>
						<?php
					else :
						wp_enqueue_script( 'hametuha-components-event-participants' );
						?>
						<div id="event-participants" data-post-id="<?php echo get_the_ID(); ?>">
						</div>
						<?php
					endif;
					?>
				<?php else : ?>
					<div class="alert alert-warning event-detail-alert">
						<p class="text-center">
							イベントに参加するには<a href="<?php echo wp_login_url( get_permalink() ); ?>" class="alert-link">ログイン</a>する必要があります。
						</p>
						<p class="text-center">
							<a class="btn btn-success btn-lg" href="<?php echo wp_login_url( get_permalink() ); ?>" rel="nofollow">ログインして参加</a>
						</p>
					</div>
				<?php endif; ?>
			<?php else : ?>
				<div class="alert alert-warning event-detail-over">
					このイベントはすでに申し込み期限を過ぎています。
				</div>
			<?php endif; ?>
		</div>

	</div>
<?php endif; ?>

<?php
if ( 2 == $announcement->commit_type ) :
	$committed_posts = $announcement->get_committed_posts();
	?>
	<?php if ( ! empty( $committed_posts ) ) : ?>
		<h2>参加している投稿
			<small><?php echo number_format_i18n( count( $committed_posts ) ); ?>件</small>
		</h2>
		<ol class="participating-posts">
			<?php
			$counter = 0;
			foreach ( $committed_posts as $p ) :
				?>
				<li class="<?php echo ( 0 == $counter % 2 ) ? 'even' : 'odd'; ?>">
					<?php echo get_avatar( $p->post_author, 20 ); ?>
					<?php echo get_the_author_meta( 'ID', $p->post_author ); ?>:
					<a href="<?php echo get_permalink( $p->ID ); ?>"><?php echo $p->post_title; ?></a>
					<small>@<?php echo mysql2date( 'Y/m/d', $p->post_date ); ?></small>
				</li>
				<?php
				++$counter;
endforeach;
			?>
		</ol>
	<?php endif; ?>
<?php endif; ?>
