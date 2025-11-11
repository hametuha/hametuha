<?php
/**
 * 星によるフィードバックを表示する
 *
 * @feature-group feedback
 */

$rating       = \Hametuha\Model\Rating::get_instance();
$rating_avg   = $rating->get_post_rating();
$rating_total = $rating->get_post_rating_count();
$all_rating   = $rating->get_post_rating_count_all();
$max_count    = 0;
foreach ( $all_rating as $count ) {
	if ( $max_count < $count ) {
		$max_count = $count;
	}
}
?>
<div class="rating">
	<h3 class="text-center"><?php esc_html_e( 'みんなの評価', 'hametuha' ); ?></h3>
	<p class="star-rating">
		<?php for ( $i = 1; $i <= 5; $i ++ ) : ?>
			<i data-value="<?php echo $i; ?>" class="icon-star6<?php echo $i <= $rating_avg ? ' active' : ''; ?>"></i>
		<?php endfor; ?>
	</p>
	<p class="rating-stats text-center text-muted" itemprop="aggregateRating" itemscope itemtype="https://schema.org/AggregateRating">
		<?php
		printf(
			__( '<span itemprop="ratingValue">%s</span>点（<span itemprop="ratingCount">%d</span>件の評価）', 'hametuha' ),
			number_format_i18n( $rating_avg, 1 ),
			$rating_total
		);
		?>
	</p>
	<?php if ( ! is_user_logged_in() ) : ?>
		<p class="text-center text-muted">
			<a href="<?php echo wp_login_url( get_permalink() ); ?>" class="alert-link">ログイン</a>すると、星の数によって冷酷な評価を突きつけることができます。
		</p>
	<?php elseif( get_current_user_id() === (int) get_the_author_meta( 'ID' ) ) : ?>
		<p class="text-center text-muted">
			※自分の作品は評価できません。
		</p>
	<?php else :
		wp_enqueue_script( 'hametuha-components-post-rating' );
		$rating = $rating->get_users_rating( get_the_ID(), get_current_user_id() );
		?>
		<div class="hametuha-post-rating" data-post-id="<?php the_ID(); ?>" data-rating="<?php echo esc_attr( $rating ); ?>"></div>
	<?php endif; ?>

	<table class="rating-table mt-3">
		<tbody>
		<?php
		foreach ( $all_rating as $score => $count ) :
			$percent = 0;
			if ( $count > 0 && $max_count > 0 ) {
				$percent = round( $count / $max_count * 100 );
			}
			?>
			<tr>
				<th>
					<?php for ( $i = 0; $i < $score; $i++ ) {
						echo '<i class="icon-star6"></i>';
					} ?>
				</th>
				<td class="rating-table-bar">
					<span class="rating-table-bar-bg">
						<span class="rating-table-bar-inner" style="width: <?php echo $percent; ?>%">&nbsp;</span>
					</span>
				</td>
				<td class="text-muted">
					<?php echo number_format( $count ); ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</div>

