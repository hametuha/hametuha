<?php if ( is_ranking( 'best' ) ) : ?>

	<ul class="nav nav-pills">
		<li class="nav-item"><a class="nav-link<?php echo ! get_query_var( 'category_name' ) ? ' active' : ''; ?>" href="<?php echo home_url( '/ranking/best/' ); ?>">全体ランキング</a></li>
		<?php
		foreach ( get_categories() as $cat ) {
			printf( '<li class="nav-item"><a class="nav-link%s" href="%s">%s部門</a></li>', get_query_var( 'category_name' ) === $cat->slug ? ' active' : '', home_url( '/ranking/best/' . $cat->slug . '/' ), esc_html( $cat->name ) );
		}
		?>
	</ul>

<?php elseif ( is_ranking( 'yearly' ) || is_ranking( 'top' ) ) : ?>
	<?php $year = is_ranking( 'top' ) ? date_i18n( 'Y' ) : get_query_var( 'year' ); ?>

	<table class="calendar-year">
		<caption><?php echo $year; ?>年月別ランキング</caption>
		<tbodY>
			<?php for ( $i = 0; $i < 2; $i++ ) : ?>
			<tr>
				<?php
				for ( $l = 1; $l <= 6; $l++ ) :
					$month = $i * 6 + $l;
					?>
					<td>
						<?php if ( $year >= date_i18n( 'Y' ) && $month > date_i18n( 'n' ) ) : ?>
							<span><?php echo $month; ?>月</span>
						<?php else : ?>
							<a href="<?php echo home_url( sprintf( '/ranking/%d/%02d/', $year, $month ) ); ?>"><?php echo $month; ?>月</a>
						<?php endif; ?>
					</td>
				<?php endfor; ?>
			</tr>
			<?php endfor; ?>
		</tbodY>
	</table>

	<ul class="pager post-pager">
		<li class="previous">
			<?php if ( $year - 1 >= 2014 ) : ?>
				<a href="<?php echo home_url( '/ranking/' . ( $year - 1 ) . '/' ); ?>">&laquo; <?php echo $year - 1; ?>年間ランキング</a>
			<?php endif; ?>
		</li>
		<li class="next">
			<?php if ( $year + 1 <= date_i18n( 'Y' ) ) : ?>
			<a href="<?php echo home_url( '/ranking/' . ( $year + 1 ) . '/' ); ?>"><?php echo $year + 1; ?>年間ランキング &raquo;</a>
			<?php endif; ?>
		</li>
	</ul>

<?php else : ?>
	<?php
		$year  = (int) get_query_var( 'year' );
		$month = (int) get_query_var( 'monthnum' );
		$tz    = wp_timezone();
		// ハイライトする期間（Y-m-d）。日別はその日、週間・先週は月曜日〜日曜日
		$hl_start = '';
		$hl_end   = '';
	if ( is_ranking( 'last_week' ) ) {
		// URL に年月が無いので、先週の日曜日の月を表示する
		list( $last_monday, $last_sunday ) = hametuha_last_week_range();
		$year                              = (int) $last_sunday->format( 'Y' );
		$month                             = (int) $last_sunday->format( 'n' );
		$hl_start                          = $last_monday->format( 'Y-m-d' );
		$hl_end                            = $last_sunday->format( 'Y-m-d' );
	} elseif ( is_ranking( 'daily' ) || is_ranking( 'weekly' ) ) {
		$queried  = new DateTimeImmutable( sprintf( '%04d-%02d-%02d', $year, $month, (int) get_query_var( 'day' ) ), $tz );
		$hl_end   = $queried->format( 'Y-m-d' );
		$hl_start = is_ranking( 'weekly' ) ? $queried->modify( '-6 days' )->format( 'Y-m-d' ) : $hl_end;
	}
		$monthnum  = ( $year * 12 ) + $month;
		$prev      = $monthnum - 1;
		$next      = $monthnum + 1;
		$prev_year = $prev % 12 ? $year : $year - 1;
		$next_year = $monthnum % 12 ? $year : $year + 1;
		$week      = [ '月', '火', '水', '木', '金', '土', '日', '週間' ];
		// 集計の始まりと、集計が確定している最後の日（3日前）
		$calc_starts = '2014-08-23';
		$fixed_until = ( new DateTimeImmutable( '@' . ( time() - 72 * HOUR_IN_SECONDS ) ) )->setTimezone( $tz )->format( 'Y-m-d' );
		$first_day   = new DateTimeImmutable( sprintf( '%04d-%02d-01', $year, $month ), $tz );
		$last_day    = $first_day->modify( 'last day of this month' )->format( 'Y-m-d' );
	?>

	<table class="calendar-year">
		<caption>
			<a href="<?php echo home_url( '/ranking/' . $year . '/' ); ?>"><?php echo $year; ?>年</a>
			<?php printf( '%d月', $month ); ?>
		</caption>
		<thead>
			<tr>
				<?php foreach ( $week as $date ) : ?>
					<th><?php echo $date; ?></th>
				<?php endforeach; ?>
			</tr>
		</thead>
		<tbody>
		<?php
		// 1日を含む週の月曜日から、月末を含む週まで1週ずつ並べる
		for ( $monday = $first_day->modify( 'monday this week' ); $monday->format( 'Y-m-d' ) <= $last_day; $monday = $monday->modify( '+1 week' ) ) :
			?>
		<tr>
			<?php
			for ( $l = 0; $l < 7; $l++ ) :
				$date     = $monday->modify( "+{$l} days" );
				$ymd      = $date->format( 'Y-m-d' );
				$in_month = (int) $date->format( 'n' ) === $month;
				$is_hl    = $in_month && $hl_start && $hl_start <= $ymd && $ymd <= $hl_end;
				?>
				<td<?php echo $is_hl ? ' class="is-highlighted"' : ''; ?>>
				<?php if ( ! $in_month ) : ?>
					&nbsp;
				<?php elseif ( is_ranking( 'daily' ) && $ymd === $hl_end ) : ?>
					<span class="on"><?php echo $date->format( 'j' ); ?></span>
				<?php elseif ( $ymd > $fixed_until || $ymd < $calc_starts ) : ?>
					<span><?php echo $date->format( 'j' ); ?></span>
				<?php else : ?>
					<a href="<?php echo home_url( '/ranking/' . $date->format( 'Y/m/d' ) . '/' ); ?>"><?php echo $date->format( 'j' ); ?></a>
				<?php endif; ?>
				</td>
			<?php endfor; ?>
			<?php
			$sunday     = $monday->modify( '+6 days' )->format( 'Y-m-d' );
			$is_week_hl = $hl_start && $sunday === $hl_end && ! is_ranking( 'daily' );
			?>
			<td<?php echo $is_week_hl ? ' class="is-highlighted"' : ''; ?>>
			<?php if ( is_ranking( 'weekly' ) && $is_week_hl ) : ?>
				<span class="on"><i class="icon-trophy-star"></i></span>
			<?php elseif ( $sunday > $fixed_until || $sunday < $calc_starts ) : ?>
				<span><i class="icon-trophy2"></i></span>
			<?php else : ?>
				<a href="<?php echo home_url( '/ranking/weekly/' . str_replace( '-', '', $sunday ) . '/' ); ?>"><i class="icon-trophy-star"></i></a>
			<?php endif; ?>
			</td>
		</tr>
		<?php endfor; ?>
		</tbody>
	</table>


	<ul class="pager post-pager">
		<li class="previous">
			<a href="<?php echo home_url( sprintf( '/ranking/%d/%02d/', $prev_year, ( $prev % 12 ?: 12 ) ) ); ?>">
				&laquo; <?php echo $prev_year; ?>年<?php echo $prev % 12 ?: 12; ?>月
			</a>
		</li>
		<li class="next">
			<?php if ( $next <= ( (int) date_i18n( 'Y' ) * 12 ) + (int) date_i18n( 'n' ) ) : ?>
			<a href="<?php echo home_url( sprintf( '/ranking/%d/%02d/', $next_year, ( $next % 12 ?: 12 ) ) ); ?>">
				<?php echo $next_year; ?>年<?php echo $next % 12 ?: 12; ?>月 &raquo;
			</a>
			<?php endif; ?>
		</li>
	</ul>

<?php endif; ?>
