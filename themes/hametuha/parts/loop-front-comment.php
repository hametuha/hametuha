<?php
/**
 * コンパクトなコメントループ
 *
 * @var array $args
 */
$args    = wp_parse_args( $args, [
	'comment' => null,
] );
$comment = get_comment( $args['comment'] );
if ( ! $comment ) {
	return;
}
?>
<li>
	<a href="<?php the_permalink( $comment->comment_post_ID ); ?>">

		<h3 class="list-heading">
			<?php
			printf(
				esc_html__( '「%s」へのコメント', 'hametuah' ),
				get_the_title( $comment->comment_post_ID )
			);
			?>
		</h3>

		<div class="list-meta">
			<?php
			echo get_avatar( $comment->user_id, 60, null, '', [ 'class' => 'mr-1' ] );
			echo esc_html( $comment->comment_author )
			?>
			<p class="mt-2">
				<small>
					<time datetime="<?php echo mysql2date( DateTime::ATOM, $comment->comment_date ); ?>">
						<?php echo mysql2date( get_option( 'date_format' ), $comment->comment_date ); ?>
					</time>
					<?php
					$count = get_comment_count( $comment->comment_post_ID );
					if ( $count['approved'] > 0 ) :
						?>
						｜
						<span class="ml-2"><i class="icon-bubble"></i> <?php printf( __( '%d件', 'hametuha' ), $count['approved'] ); ?></span>
					<?php endif; ?>
				</small>
			</p>
		</div>

		<div class="list-excerpt">
			<?php echo esc_html( trim_long_sentence( hametuha_censor( $comment->comment_content ), 88 ) ); ?>
		</div>
	</a>
</li>
