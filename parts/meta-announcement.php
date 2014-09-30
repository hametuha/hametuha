<?php
/** @var $announcement \Hametuha\ThePost\Announcement */
$announcement = $post->helper;
?>

<div class="event-detail clearfix">

	<dl class="dl-horizontal">


		<?php
            $start = lwp_event_starts('Y/n/j (D) H:i');
            $end = lwp_event_ends('Y/n/j (D) H:i');
			$expired = lwp_event_ends('U') < current_time('timestamp');
		?>
        <?php if( $start || $end ): ?>
            <dt>日時</dt>
            <dd>
                <?php
                    echo $start." 〜 ".$end;
                ?>
                <?php if( $expired ): ?>
                <span class="label label-danger">終了</span>
                <?php endif; ?>
            </dd>
        <?php elseif( $announcement->is_limited() ): ?>
            <dt>日時</dt>
            <dd>
                <?= $announcement->get_period(); ?>
                <?php if( $announcement->is_expired() ): ?>
                <span class="label label-danger">終了</span>
                <?php endif; ?>
            </dd>
        <?php endif;?>

		<?php if( $announcement->has_place() ): ?>
			<dt>場所</dt>
			<dd>
				<a href="http://www.google.co.jp/maps?q=<?= rawurlencode($post->helper->get_address(false) ) ?>" class="small-button" target="_blank"><?= $post->helper->get_address() ?></a>
			</dd>
		<?php endif; ?>

		<?php if( $announcement->is_participating() ):?>
			<dt>募集期間</dt>
			<dd>
				<?= $announcement->get_participating_period() ?>
				<?php if( $announcement->left_second_to_participate() == 0 ): ?>
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
		<?php endif; ?>

		<dt>備考</dt>
		<dd><?= $announcement->notice ?></dd>

	</dl>

	<?php if( $announcement->has_place() ): ?>
		<div id="gmap-announcement" class="row" data-address="<?= $announcement->get_address(true) ?>"></div>
	<?php endif; ?>
</div>




<?php if( 2 == $announcement->commit_type ): $committed_posts = $announcement->get_committed_posts(); ?>
    <?php if(!empty($committed_posts)): ; ?>
    <h2>参加している投稿 <small><?php echo number_format_i18n( count($committed_posts) );?>件</small></h2>
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
