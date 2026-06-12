<?php
/**
 * 合評会の当日採点を表示する
 *
 * 状態に応じて表示を切り替える。
 * - open      : 当日参加者には入力フォーム、それ以外には進捗のみ（点数は隠す）
 * - published : 事前点数＋当日点の結果テーブルを公開（リビール演出）
 *
 * @feature-group joint-review
 */

$jr_term = get_queried_object();
if ( ! $jr_term || 'campaign' !== $jr_term->taxonomy ) {
	return;
}
$jr_state     = hametuha_jr_state( $jr_term );
$jr_works     = hametuha_jr_works( $jr_term );
$jr_allotment = hametuha_jr_allotment( $jr_term );
$jr_total     = count( hametuha_jr_participants( $jr_term ) );
$jr_voted     = count( hametuha_jr_voters( $jr_term ) );
$jr_user_id   = get_current_user_id();
?>

<div class="campaign-review-live">

<?php if ( 'open' === $jr_state ) : ?>

	<h3 class="campaign-review-live__title">
		<i class="icon-pencil"></i> <?php esc_html_e( '合評会 採点受付中', 'hametuha' ); ?>
	</h3>

	<?php if ( ! is_user_logged_in() ) : ?>

		<p class="alert alert-info">
			<?php
			printf(
				/* translators: %s: login url */
				wp_kses_post( __( '採点状況は<a href="%s" class="alert-link">ログイン</a>すると確認できます。', 'hametuha' ) ),
				esc_url( wp_login_url( get_term_link( $jr_term ) ) )
			);
			?>
		</p>

	<?php elseif ( hametuha_jr_is_participant( $jr_term, $jr_user_id ) ) : ?>

		<?php
		// 入力フォーム用のデータを用意する。
		$jr_works_data = [];
		foreach ( $jr_works as $jr_post_id => $jr_author ) {
			$jr_works_data[] = [
				'id'    => $jr_post_id,
				'title' => get_the_title( $jr_post_id ),
				'own'   => ( (int) $jr_author === (int) $jr_user_id ),
			];
		}
		$jr_distribution = hametuha_jr_user_distribution( $jr_term, $jr_user_id );
		wp_enqueue_script( 'hametuha-components-joint-review-input' );
		?>
		<p class="campaign-review-live__lead">
			<?php
			printf(
				/* translators: %s: allotment */
				esc_html__( 'あなたの持ち点は %s 点です。自分の作品以外に、ちょうど使い切るように配分してください。', 'hametuha' ),
				'<strong>' . esc_html( number_format_i18n( $jr_allotment ) ) . '</strong>'
			);
			?>
		</p>
		<div class="hametuha-joint-review-input"
				data-term-id="<?php echo esc_attr( $jr_term->term_id ); ?>"
				data-allotment="<?php echo esc_attr( $jr_allotment ); ?>"
				data-works="<?php echo esc_attr( wp_json_encode( $jr_works_data ) ); ?>"
				data-distribution="<?php echo esc_attr( wp_json_encode( (object) $jr_distribution ) ); ?>"></div>

	<?php else : ?>

		<p class="alert alert-warning">
			<?php esc_html_e( '採点できるのは当日参加者のみです。結果の公開をお待ちください。', 'hametuha' ); ?>
		</p>

	<?php endif; ?>

	<p class="campaign-review-live__progress text-muted">
		<?php
		printf(
			/* translators: %1$d: voted, %2$d: total */
			esc_html__( '採点入力：%1$d / %2$d 名', 'hametuha' ),
			(int) $jr_voted,
			(int) $jr_total
		);
		?>
	</p>

<?php elseif ( 'published' === $jr_state ) : ?>

	<?php
	$jr_result = hametuha_joint_review_result( $jr_term );
	if ( is_wp_error( $jr_result ) || empty( $jr_result['works'] ) ) {
		echo '<p class="alert alert-default">' . esc_html__( '結果がありません。', 'hametuha' ) . '</p>';
		return;
	}
	// 合計（結果）が高い順に作品を並べる。
	$jr_ordered = $jr_result['result'];
	arsort( $jr_ordered );
	$jr_rank = 0;
	?>

	<h3 class="campaign-review-live__title">
		<i class="icon-trophy"></i> <?php esc_html_e( '合評会 最終結果', 'hametuha' ); ?>
	</h3>

	<div class="campaign-score-wrapper">
		<table class="campaign-score campaign-score--reveal">
			<caption><?php esc_html_e( '事前点数と当日点の合計', 'hametuha' ); ?></caption>
			<thead>
				<tr>
					<th class="campaign-score-user">&nbsp;</th>
					<?php foreach ( array_keys( $jr_ordered ) as $jr_post_id ) : ?>
						<th class="campaign-score-work">
							<a href="<?php echo esc_url( get_permalink( $jr_post_id ) ); ?>"><?php echo esc_html( get_the_title( $jr_post_id ) ); ?></a>
						</th>
					<?php endforeach; ?>
					<th class="campaign-score-subtotal"><?php esc_html_e( '持ち点', 'hametuha' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $jr_result['participants'] as $jr_uid => $jr_dist ) : ?>
					<?php
					$jr_user   = get_userdata( $jr_uid );
					$jr_sum    = array_sum( $jr_dist );
					$jr_author = in_array( (int) $jr_uid, array_map( 'intval', array_values( $jr_result['works'] ) ), true );
					?>
					<tr style="--jr-row: <?php echo (int) ( $jr_rank++ ); ?>;">
						<th class="campaign-score-user">
							<?php echo get_avatar( $jr_uid, 32 ); ?>
							<span class="campaign-score-user-title">
								<?php echo esc_html( $jr_user ? $jr_user->display_name : '退会者' ); ?>
								<?php if ( $jr_author ) : ?>
									<span class="label label-danger">書</span>
								<?php else : ?>
									<span class="label label-default">読</span>
								<?php endif; ?>
							</span>
						</th>
						<?php foreach ( array_keys( $jr_ordered ) as $jr_post_id ) : ?>
							<td class="campaign-score-post <?php echo ( (int) $jr_result['works'][ $jr_post_id ] === (int) $jr_uid ) ? 'campaign-score-own' : ''; ?>">
								<?php echo esc_html( number_format_i18n( isset( $jr_dist[ $jr_post_id ] ) ? $jr_dist[ $jr_post_id ] : 0, 1 ) ); ?>
							</td>
						<?php endforeach; ?>
						<td class="campaign-score-post"><?php echo esc_html( number_format_i18n( $jr_sum, 1 ) ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
			<tfoot>
				<tr class="campaign-score-pre">
					<th class="campaign-score-user"><?php esc_html_e( '事前点数', 'hametuha' ); ?></th>
					<?php foreach ( array_keys( $jr_ordered ) as $jr_post_id ) : ?>
						<td class="campaign-score-post"><?php echo esc_html( number_format_i18n( $jr_result['pre'][ $jr_post_id ], 1 ) ); ?></td>
					<?php endforeach; ?>
					<td>&nbsp;</td>
				</tr>
				<tr class="campaign-score-result">
					<th class="campaign-score-user"><strong><?php esc_html_e( '結果', 'hametuha' ); ?></strong></th>
					<?php
					$jr_top = true;
					foreach ( array_keys( $jr_ordered ) as $jr_post_id ) :
						?>
						<td class="campaign-score-post">
							<strong><?php echo esc_html( number_format_i18n( $jr_result['result'][ $jr_post_id ], 1 ) ); ?></strong>
							<?php if ( $jr_top ) : ?>
								<span class="label label-warning">優勝</span>
								<?php
								$jr_top = false;
							endif;
							?>
						</td>
					<?php endforeach; ?>
					<td>&nbsp;</td>
				</tr>
			</tfoot>
		</table>
	</div><!-- //.campaign-score-wrapper -->

	<style>
		.campaign-score--reveal tbody tr {
			animation: jrFadeIn .4s ease both;
			animation-delay: calc( var( --jr-row, 0 ) * .12s );
		}
		.campaign-score--reveal tfoot .campaign-score-result {
			animation: jrFadeIn .6s ease both;
			animation-delay: .8s;
		}
		@keyframes jrFadeIn {
			from { opacity: 0; transform: translateY( .5em ); }
			to   { opacity: 1; transform: translateY( 0 ); }
		}
		@media ( prefers-reduced-motion: reduce ) {
			.campaign-score--reveal tbody tr,
			.campaign-score--reveal tfoot .campaign-score-result { animation: none; }
		}
	</style>

<?php endif; ?>

</div><!-- //.campaign-review-live -->
