<?php
/** @var $announcement \Hametuha\ThePost\Announcement */
$announcement = $post->helper;


get_template_part( 'parts/event', 'address' )

?>

<?php
if ( $announcement->is_participating() ) :
	$rest_time_for_limit = $announcement->left_second_to_participate();
	// enqueue
	wp_enqueue_script( 'hamevent' );
	wp_localize_script( 'hamevent', 'HamEvent', [
		'inList'       => $announcement->in_list( get_current_user_id() ),
		'event'        => get_the_ID(),
		'text'         => $announcement->guest_comment( get_current_user_id() ),
		'participants' => $announcement->get_participants(),
		'limit'        => $announcement->participating_limit(),
	] );
	?>

	<div ng-controller="hameventStatus">

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
					<div ng-cloak>

					{{participants.length}} / {{limit}} 名
					<?php if ( current_user_can( 'read' ) ) : ?>
						<div class="event-detail-list" ng-if="participants.length">
							<div class="event-detail-user" ng-repeat="user in participants">
								<img ng-src="{{user.avatar}}" uib-tooltip="{{user.text}}">
								<strong><a ng-href="{{user.url}}">{{user.name}}</a></strong>
							</div>
						</div>
					<?php endif; ?>
					</div>
				</td>
			</tr>
		</table>

		<div class="event-detail clearfix" ng-cloak>
			<?php if ( 0 < $rest_time_for_limit ) : ?>
				<?php if ( current_user_can( 'read' ) ) : ?>
					<div class="alert alert-info event-detail-alert" ng-if="limit <= participants.length">
						<p class="text-center">
							このイベントはすでに定員を超過しています。
						</p>
					</div>
					<div ng-class="loading ? 'loading' : ''">
						<div class="text-center" ng-if="inList">
							<span class="text-success text-lg">参加しています</span>
							<button class="btn btn-delete btn-sm" ng-click="getOut()">キャンセル</button>
						</div>
						<div class="" ng-if="(!inList) && (participants.length < limit)">
							<button class="btn btn-success btn-lg btn-block" ng-click="getIn()">参加する</button>
						</div>
						<div class="form-group event-detail-comment">
							<label>参加コメント</label>
							<textarea class="form-control" ng-model="text"
									  ng-keyup="updateComment()"></textarea>
							<div class="form-helper">参加にあたってなにかコメントがあれば書いてください。ログインしているユーザーに表示されます。</div>
						</div>
					</div>
					<?php if ( current_user_can( 'edit_others_posts' ) ) : ?>
						<div>
						</div>
					<?php endif; ?>
				<?php else : ?>
					<div class="alert alert-warning event-detail-alert">
						<p class="text-center">
							イベントに参加するには<a href="<?php echo wp_login_url( get_permalink() ); ?>" class="alert-link">ログイン</a>する必要があります。
						</p>
						<p class="text-center">
							<a class="btn btn-success btn-lg" href="<?php echo wp_login_url( get_permalink() ); ?>">ログインして参加</a>
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
				$counter ++;
endforeach;
			?>
		</ol>
	<?php endif; ?>
<?php endif; ?>
