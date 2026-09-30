<?php
/**
 * ランキングトップ
 *
 * @feature-group ranking
 */
?>
<!-- 先週のランキング -->
<?php
$prev_thursday = new DateTimeImmutable( 'previous thursday', wp_timezone() );
$sunday        = $prev_thursday->modify( 'previous sunday' )->getTimestamp();
$monday        = $prev_thursday->modify( 'previous sunday' )->modify( 'previous monday' )->getTimestamp();
$latest_week   = new WP_Query([
	'ranking'        => 'last_week',
	'posts_per_page' => 3,
]);
if ( $latest_week->have_posts() ) :
	?>
	<h2 class="archive-ranking-title"><i class="icon-calendar4"></i> 最新週間ランキング <span class="badge text-bg-success">確定済み</span></h2>
	<p><?php echo wp_date( 'Y年n月j日（D）', $monday ); ?>〜<?php echo wp_date( 'Y年n月j日（D）', $sunday ); ?></p>
	<ol class="archive-container media-list">
		<?php
		while ( $latest_week->have_posts() ) :
			$latest_week->the_post();
			?>
			<?php get_template_part( 'parts/loop', 'ranking' ); ?>
			<?php
		endwhile;
		wp_reset_postdata();
		?>
	</ol>
	<p>
		<a class="btn btn-default btn-lg btn-block" href="<?php echo home_url( '/ranking/weekly/' . wp_date( 'Ymd/', $sunday ) ); ?>">最新週間ランキングを見る</a>
	</p>

	<hr />

<?php endif; ?>

<!-- 直近のランキング -->
<?php
$latest_date = time() - 60 * 60 * 24 * 4;
$latest_day  = new WP_Query([
	'ranking'        => 'daily',
	'year'           => wp_date( 'Y', $latest_date ),
	'monthnum'       => wp_date( 'm', $latest_date ),
	'day'            => wp_date( 'd', $latest_date ),
	'posts_per_page' => 3,
]);
if ( $latest_day->have_posts() ) :
	?>
	<h2 class="archive-ranking-title"><?php echo wp_date( 'Y年n月j日（D）', $latest_date ); ?>のランキング</h2>
	<ol class="archive-ranking">
		<?php
		while ( $latest_day->have_posts() ) :
			$latest_day->the_post();
			?>
			<?php get_template_part( 'parts/loop', 'ranking' ); ?>
			<?php
		endwhile;
		wp_reset_postdata();
		?>
	</ol>
	<p class="text-center">
		<a class="btn btn-default btn-lg" href="<?php echo home_url( '/ranking' . wp_date( '/Y/m/d/', $latest_date ) ); ?>">
			<?php echo wp_date( 'Y年n月j日（D）', $latest_date ); ?>のランキングを見る
		</a>
	</p>

<?php endif; ?>


<!-- 今月のランキング -->
<?php
$this_month   = date_i18n( 'j' ) >= 5 ? time() : time() - ( 60 * 60 * 24 * 5 );
$latest_month = new WP_Query([
	'ranking'        => 'monthly',
	'year'           => wp_date( 'Y', $this_month ),
	'monthnum'       => wp_date( 'm', $this_month ),
	'posts_per_page' => 3,
]);
if ( $latest_month->have_posts() ) :
	?>
	<h2 class="archive-ranking-title"><?php echo wp_date( 'Y年n月', $this_month ); ?>のランキング</h2>
	<ol class="archive-ranking">
		<?php
		while ( $latest_month->have_posts() ) :
			$latest_month->the_post();
			?>
			<?php get_template_part( 'parts/loop', 'ranking' ); ?>
			<?php
		endwhile;
		wp_reset_postdata();
		?>
	</ol>
	<p class="text-center">
		<a class="btn btn-default btn-lg" href="<?php echo home_url( '/ranking' . wp_date( '/Y/m/', $this_month ) ); ?>">
			<?php echo wp_date( 'Y年n月', $this_month ); ?>のランキングを見る
		</a>
	</p>

<?php endif; ?>

<!-- 歴代ベスト -->
<?php
$bests = new WP_Query([
	'ranking'        => 'best',
	'post_type'      => 'post',
	'post_status'    => 'publish',
	'posts_per_page' => 3,
]);
if ( $bests->have_posts() ) :
	?>
	<h2 class="archive-ranking-title">歴代ランキング</h2>
	<ol class="archive-ranking">
		<?php
		while ( $bests->have_posts() ) :
			$bests->the_post();
			?>
			<?php get_template_part( 'parts/loop', 'ranking' ); ?>
			<?php
		endwhile;
		wp_reset_postdata();
		?>
	</ol>
	<p class="text-center">
		<a class="btn btn-default btn-lg" href="<?php echo home_url( '/ranking/best/' ); ?>">
			歴代ランキングを見る
		</a>
	</p>
<?php endif; ?>
