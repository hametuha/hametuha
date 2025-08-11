<?php
/**
 * 公募の採点結果を表示する
 *
 * @param array $atts
 */

$records = hametuha_campaign_record();
if ( ! $records ) {
	return;
} elseif ( is_wp_error( $records ) ) {
	printf( '<p class="alert alert-default">%s</p>', esc_html( $records->get_error_message() ) );
	return;
}
?>

<div class="campaign-score-wrapper">

	<table class="campaign-score">

		<caption>現在の採点結果</caption>

		<thead>
			<tr>
				<th class="campaing-score-user">&nbsp;</th>
				<?php foreach ( $records['posts'] as $post_id => $author ) : ?>
					<th class="campaign-score-work"><a href="<?php echo get_permalink( $post_id ); ?>"><?php echo get_the_title( $post_id ); ?></a></th>
				<?php endforeach; ?>
				<th class="campaign-score-subtotal">持ち点</th>
			</tr>
		</thead>
		<tfoot>
		<?php
			$out   = '';
			$total = [];
		foreach ( $records['participants'] as $user_id => $var ) {
			$user = get_userdata( $user_id );
			ob_start();
			?>
				<tr>
					<th class="campaign-score-user">
					<?php if ( $var['author'] ) : ?>
							<?php echo get_avatar( $user_id ); ?>
						<?php else : ?>
							<img src="<?php echo get_template_directory_uri(); ?>/assets/img/mystery-man.png" alt="" class="avatar" />
						<?php endif; ?>
						<span class="campaign-score-user-title">

						<?php
						if ( $var['author'] ) {
							echo esc_html( $user->display_name );
						} else {
							echo '読み専';
						}
						?>

						<?php if ( $var['author'] ) : ?>
								<span class="label label-danger">書</span>
							<?php else : ?>
								<span class="label label-default">読</span>
							<?php endif; ?>
						</span>
					</th>
					<?php foreach ( $records['posts'] as $post_id => $author ) : ?>
						<td class="campaign-score-post <?php echo $author == $user_id ? 'campaign-score-own' : ''; ?>">
							<?php
							$score = ( isset( $var['rate_total'] ) && $var['rate_total'] > 0 ) ? ( $var['comment_total'] + 1 ) * $var['records'][ $post_id ] / $var['rate_total'] : 0;
							if ( ! isset( $total[ $post_id ] ) ) {
								$total[ $post_id ] = 0;
							}
							$total[ $post_id ] += $score;
							echo number_format_i18n( $score, 1 );
							?>
						</td>
					<?php endforeach; ?>
					<td class="campaign-score-post"><?php echo number_format_i18n( $var['comment_total'] + 1, 1 ); ?></td>
				</tr>
				<?php
				$out .= ob_get_contents();
				ob_end_clean();
		}
		?>
			<td class="campaign-score-user"><?php echo number_format( count( $records['participants'] ) ); ?>名</td>
			<?php foreach ( $total as $post_id => $score ) : ?>
				<td class="campaign-score-post"><?php echo number_format( $score, 1 ); ?></td>
			<?php endforeach; ?>
			<td>&nbsp;</td>
		</tfoot>

		<tbody>
		<?php echo $out; ?>
		</tbody>

	</table>

</div><!-- campaign-score-wrapper -->
