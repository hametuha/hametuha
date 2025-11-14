<?php
/*
 * ランキング用ループ
 */

$rank       = get_the_ranking();
$rank_class = 'rank-list-score-normal';
if ( in_array( $rank, [ 1, 2, 3 ], true ) ) {
	$rank_class = 'rank-list-score-' . $rank;
}
switch ( strlen( $rank ) ) {
	case 1:
	case 2:
		// 2桁までならオーケー
		$rank_length = '';
		break;
	case 3:
		$rank_length = 'rank-list-score-digit3';
		break;
	case 4:
		$rank_length = 'rank-list-score-digit4';
		break;
	default:
		$rank_length = 'rank-list-score-digit6';
		break;
}
?>
<li <?php post_class( 'rank-list' ); ?>>

	<a class="rank-list-link" href="<?php the_permalink(); ?>">

		<div class="rank-list-header">

			<div class="rank-list-score <?php echo esc_attr( $rank_class ); ?>">
				<strong class="<?php echo esc_attr( $rank_length ); ?>"><?php echo $rank; ?></strong>
			</div>

			<div class="rank-list-status">
				<?php
				if ( is_null( $post->transition ) ) {
					$icon = 'icon-new';
				} elseif ( -1 >= $post->transition ) {
					$icon = 'icon-arrow-down-right2';
				} elseif ( 1 <= $post->transition ) {
					$icon = 'icon-arrow-up-right2';
				} else {
					$icon = 'icon-arrow-right5';
				}
				printf( '<i class="rank-status %s"></i>', esc_attr( $icon ) );
				?>
			</div>
		</div><!-- rank list header. -->

		<div class="rank-list-body">

			<!-- Title -->
			<h2 class="rank-list-title">
				<?php
				echo hametuha_censor( get_the_title() );
				$categories = get_the_category();
				if ( $categories && ! is_wp_error( $categories ) ) {
					printf( '<small>%s</small>', implode( ', ', array_map( function ( WP_Term $category ) {
						return esc_html( $category->name );
					}, $categories ) ) );
				}
				?>
			</h2>

			<!-- Post Data -->
			<ul class="list-inline rank-list-meta">
				<li class"author-info">
					<?php echo get_avatar( get_the_author_meta( 'ID' ), 40 ); ?>
					<?php the_author(); ?>
				</li>
				<li class="date">
					<i class="icon-calendar2"></i> <?php echo hametuha_passed_time( $post->post_date ); ?>
					<?php if ( is_recent_date( $post->post_date, 3 ) ) : ?>
						<span class="label label-danger">New!</span>
					<?php elseif ( is_recent_date( $post->post_modified, 7 ) ) : ?>
						<span class="label label-info">更新</span>
					<?php endif; ?>
				</li>
				<li class="static">
					<i class="icon-reading"></i> <?php echo number_format( get_post_length() ); ?>文字
				</li>
				<?php if ( current_user_can( 'edit_others_posts' ) ) : ?>
					<li>
						<?php echo number_format( $post->pv ); ?>PV
					</li>
				<?php endif; ?>
			</ul>

			<!-- Excerpt -->
			<div class="archive-excerpt">
				<p class="text-muted"><?php echo hametuha_censor( trim_long_sentence( get_the_excerpt(), 98 ) ); ?></p>
			</div>

		</div>
	</a>
</li>
