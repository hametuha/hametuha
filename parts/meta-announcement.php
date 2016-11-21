<?php
/** @var $announcement \Hametuha\ThePost\Announcement */
$announcement = $post->helper;


get_template_part( 'parts/event', 'address' )

?>

<?php if ( $announcement->is_participating() ) : ?>
<div class="event-detail clearfix">

	<dl class="dl-horizontal">

			<dt>募集期間</dt>
			<dd>
				<?= $announcement->get_participating_period() ?>
				<?php if ( $announcement->left_second_to_participate() == 0 ) : ?>
					<span class="label label-danger">終了</span>
				<?php endif; ?>
			</dd>
			<dt>応募条件</dt>
			<dd>
				<?= $announcement->participating_condition() ?>
			</dd>
			<dt>参加費用</dt>
			<dd>
				<?= $announcement->participating_cost() ?>
			</dd>
			<dt>定員</dt>
			<dd>
				<?= $announcement->participating_limit() ?>
			</dd>
	</dl>

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
