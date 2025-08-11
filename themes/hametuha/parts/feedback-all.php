<div itemprop="review" itemscope itemtype="http://schema.org/Review">

	<h3 class="text-center" itemprop="author">みんなの反応</h3>

	<?php
	if ( is_null( $all_rating ) ) {
		printf( '<div class="alert alert-warning"><p>この作品にはまだレビューがありません。ぜひレビューを残してください。</p></div>' );
	} else {
		$star = round( $all_rating * 2 ) / 2;
		?>
		<p class="post-rank-counter text-center" itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating">
			<span class="back-ground">
				<?php
				for ( $i = 0; $i < 5; $i ++ ) {
					echo '<i class="icon-star6"></i>';
				}
				?>
			</span>
			<span class="fore-ground">
				<?php
				while ( $star > 0 ) {
					if ( $star > 0.5 ) {
						$star_class = 6;
					} else {
						$star_class = 5;
					}
					printf( '<i class="icon-star%d"></i>', $star_class );
					$star -= 1;
				}
				?>
			</span>
			<strong itemprop="ratingValue"><?php echo ( number_format_i18n( $all_rating, 1 ) ); ?></strong>
			<?php if ( $total ) : ?>
				<small>(<?php echo number_format_i18n( $total ); ?>件の評価)</small>
			<?php endif; ?>
		</p><!-- //.post-rank-counter -->
		<?php
	}
	?>




<?php echo $chart; ?>

<p class="text-center text-muted">
	<small>破滅チャートとは<?php help_tip( '破滅派読者が入力した感想を元に生成されるチャートです。赤いほど破滅度が高く、青いほど健全な作品です。' ); ?></small>
</p>

</div>
