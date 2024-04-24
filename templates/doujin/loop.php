<?php
/**
 * Loop template for doujin.
 *
 * @var array{author:WP_User} $args;
 */

$author = $args['author'] ?? null;
if ( ! $author ) {
	return;
}
$ruby = $author->last_name;
?>
<li class="author-group-list-item">

	<?php echo get_avatar( $author->ID, 96, '', $author->display_name, [ 'class' => 'author-group-list-avatar' ] ); ?>

	<a class="author-group-list-link" href="<?php echo esc_url( hametuha_author_url( $author->ID ) ); ?>">
		<?php if ( $ruby ) : ?>
			<ruby class="author-group-list-title"><?php echo esc_html( $author->display_name ); ?><rp>（</rp><rt><?php echo esc_html( $ruby ); ?></rt><rp>）</rp></ruby>
		<?php else: ?>
			<span class="author-group-list-title"><?php echo esc_html( $author->display_name ); ?></span>
		<?php endif; ?>

		<small class="author-group-list-role"><?php echo hametuha_user_role( $author ); ?></small>
	</a>

	<p class="author-group-list-description">
		<?php echo esc_html( wp_trim_words( $author->description, 80 ) ); ?>
	</p>

	<p class="author-group-list-meta">
		<?php
			foreach ( hametuha_user_flags( true ) as $flag ) {
				if ( get_user_meta( $author->ID, 'flag_' . $flag['id'], true ) ) {
					printf( '<span class="author-group-list-flag">%s</span>', esc_html( $flag['label'] ) );
				}
			}
		?>
		<span class="author-group-list-date">
			<?php
			printf( esc_html__( '%s登録', 'hametuha' ), mysql2date( get_option( 'date_format' ), $author->user_registered ) );
			?>
		</span>
	</p>
</li>
