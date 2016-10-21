<?php
if ( ! ( $records = hametuha_campaign_record() ) ) {
	return;
} else if ( is_wp_error( $records ) ) {
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
					<th class="campaign-score-work"><a href="<?= get_permalink( $post_id ) ?>"><?= get_the_title( $post_id ) ?></a></th>
				<?php endforeach; ?>
				<th class="campaign-score-subtotal">小計</th>
			</tr>
		</thead>
		<tfoot>
		<?php
			$out = '';
			$total = [];
			foreach ( $records['participants'] as $user_id => $var ) {
				$user = get_userdata( $user_id );
				ob_start();
				?>
				<tr>
					<th class="campaign-score-user">
						<?= get_avatar( $user_id ) ?>
						<span class="campaign-score-user-title">
							<?= esc_html( $user->display_name ) ?>
						</span>
					</th>
					<?php foreach ( $records['posts'] as $post_id => $author ) : ?>
						<td class="campaign-score-post <?= $author == $user_id ? 'campaign-score-own' : '' ?>">
							<?php
							$score = $var['rate_total'] > 0 ? $var['comment_total'] * $var['records'][ $post_id ] / $var['rate_total'] : 0;
							if ( ! isset( $total[ $post_id ] ) ) {
								$total[ $post_id ] = 0;
							}
							$total[ $post_id ] += $score;
							echo number_format_i18n( $score, 1 );
							?>
						</td>
					<?php endforeach; ?>
					<td class="campaign-score-post"><?= number_format_i18n( $var['comment_total'], 1 ) ?></td>
				</tr>
				<?php
				$out .= ob_get_contents();
				ob_end_clean();
			}
		?>
			<td class="campaign-score-user"><?= number_format( count( $records['participants'] ) ) ?>名</td>
			<?php foreach ( $total as $post_id => $score ) : ?>
				<td class="campaign-score-post"><?= number_format( $score, 1 ) ?></td>
			<?php endforeach; ?>
			<td>&nbsp;</td>
		</tfoot>

		<tbody>
		<?= $out; ?>
		</tbody>

	</table>

</div>


