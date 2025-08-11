<?php
/**
 * ユーザーの一覧を表示する
 *
 * @var array{users: WP_User[] } $args
 */
$users = $args['users'] ?? [];
if ( empty( $users ) ) {
	return;
}
?>
<div class="event-detail-list">
	<?php foreach ( $users as $user ) : ?>
		<div class="event-detail-user">
			<?php echo get_avatar( $user->ID ); ?>
			<strong>
				<?php
				if ( user_can( $user, 'edit_posts' ) ) {
					printf(
						'<a href="%s">%s</a>',
						esc_url( hametuha_author_url( $user->ID ) ),
						esc_html( $user->display_name )
					);
				} else {
					echo esc_html( $user->display_name );
				}
				?>
			</strong>
		</div>
	<?php endforeach; ?>
</div>
