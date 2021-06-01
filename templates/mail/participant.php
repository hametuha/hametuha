<?php
/**
 * @var WP_Post $post
 * @var bool $status
 * @var WP_User $participant
 * @var WP_User $organizer
 * @var bool $update
 * @var string $message
 */
?>
<?php echo esc_html( $participant->display_name ); ?> さんの参加状況です。


<?php
printf(
	'%s: %s',
	$update ? '更新' : '新規',
	$status ? '<strong>参加</strong>' : '<em>不参加</em>'
);
?>


---------
<?php echo $message; ?>

---------

返信はこちら <?php echo $participant->user_email; ?>
