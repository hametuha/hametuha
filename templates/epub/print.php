<?php
/**
 * 印刷用テンプレート
 */
get_header( 'meta' );
$total_length = 0;
?>

<div class="print-wrapper container">
	<header class="print-header row">
		<div class="col-xs-4">
			<button class="btn btn-default" onclick="window.close()"><i class="icon-close"></i> 閉じる</button>
		</div>
		<div class="col-xs-4 text-center">
			<?= get_the_title( $series ) ?>
		</div>
		<div class="col-xs-4 text-right">
			<button class="btn btn-primary" onclick="window.print()"><i class="icon-print"> 印刷</i></button>
		</div>

	</header>
	<?php while ( $query->have_posts() ) :
		$query->the_post();
		?>
		<div class="print-title">
			<?php the_title() ?>
		</div>
		<div class="print-meta row">
			<div class="col-xs-4 print-letter">
				<?php
				$length = get_post_length();
				$total_length += $length;
				printf( '%s文字', number_format( $length ) );
				?>
			</div>
			<div class="col-xs-4 print-author">
				<?php the_author() ?>
			</div>
			<div class="col-xs-4 print-date">
				<?php the_time( 'Y-m-d H:i' ) ?>
			</div>
		</div>
		<div class="work-content print-content">
			<?php the_content(); ?>
			<?php for ( $i = 1; $i < 20; $i ++ ) : ?>
				<div class="print-wedge" style="top: <?= $i * 5 ?>%">
					<?= $i * 5 ?>%
				</div>
			<?php endfor; ?>
		</div>
	<?php endwhile; ?>
	<footer class="print-footer row">
		<div class="col-xs-6 print-total">
			<strong><?= number_format( $total_length / 400, 1 ) ?>枚</strong>
			<small>（<?= number_format( $total_length ) ?>文字）</small>
		</div>
		<div class="col-xs-6 text-right">
			計<?= number_format_i18n( $query->post_count ) ?>作品
			<small>（平均<?= number_format_i18n( $total_length / $query->post_count, 1 ) ?>文字）</small>
		</div>
	</footer>
</div>
<?php wp_footer(); ?>
</body>
</html>
