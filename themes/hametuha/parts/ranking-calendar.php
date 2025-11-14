<?php if ( is_ranking( 'best' ) ) : ?>

	<ul class="nav nav-pills">
		<li class="<?php echo ! get_query_var( 'category_name' ) ? ' active' : ''; ?>"><a href="<?php echo home_url( '/ranking/best/' ); ?>">全体ランキング</a></li>
		<?php
		foreach ( get_categories() as $cat ) {
			printf( '<li class="%s"><a href="%s">%s部門</a></li>', get_query_var( 'category_name' ) == $cat->slug ? 'active' : '', home_url( '/ranking/best/' . $cat->slug . '/' ), esc_html( $cat->name ) );
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
		$year           = get_query_var( 'year' );
		$month          = get_query_var( 'monthnum' );
		$day            = get_query_var( 'day' );
		$monthnum       = ( $year * 12 ) + $month;
		$prev           = $monthnum - 1;
		$next           = $monthnum + 1;
		$prev_year      = $prev % 12 ? $year : $year - 1;
		$next_year      = $monthnum % 12 ? $year : $year + 1;
		$calc_starts    = strtotime( '2014-08-23 00:00:00' );
		$week           = [ '月', '火', '水', '木', '金', '土', '日', '週間' ];
		$start_of_month = sprintf( '%d-%02d-01 00:00:00', $year, $month );
		$limit_of_month = date_i18n( 't', mktime( 0, 0, 0, $month, 1, $year ) );
		$start_of_date  = array_search( date_i18n( 'D', strtotime( $start_of_month ) ), $week ) + 1;
		$starting       = false;
		$ended          = false;
		$out_date       = 0;
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
		<?php for ( $i = 0; $i < 5; $i++ ) : ?>
		<tr>
			<?php for ( $l = 1; $l <= 7; $l++ ) : ?>
				<?php
				if ( ! $starting && $l === $start_of_date && ! $ended ) {
					$starting = true;
				} elseif ( $starting && $out_date >= $limit_of_month ) {
					$ended = true;
				}
					$unfixed = true;
				?>
				<td>
						<?php
						if ( $starting && ! $ended ) :
							++$out_date;
							$calc_date     = current_time( 'timestamp' ) - 60 * 60 * 72;
							$date_to_ouput = strtotime( sprintf( '%d/%02d/%02d', $year, $month, $out_date ) );
							if ( $out_date == get_query_var( 'day' ) ) :
								?>
									<span class="on"><?php echo $out_date; ?></span>
									<?php
								elseif ( $date_to_ouput > $calc_date || $date_to_ouput < $calc_starts ) :
									?>
									<span><?php echo $out_date; ?></span>
									<?php
									else :
										$unfixed = false;
										?>
									<a href="<?php echo home_url( sprintf( '/ranking/%d/%02d/%02d/', $year, $month, $out_date ) ); ?>"><?php echo $out_date; ?></a>
								<?php endif; ?>
						<?php else : ?>
							&nbsp;
						<?php endif; ?>
				</td>
				<?php if ( $l == 7 ) : ?>
					<?php if ( ! $unfixed ) : ?>
						<td><a href="<?php echo home_url( sprintf( '/ranking/weekly/%04d%02d%02d/', $year, $month, $out_date ) ); ?>"><i class="icon-trophy-star"></i></a></td>
					<?php else : ?>
						<td><span><i class="icon-trophy2"></i></span></td>
					<?php endif; ?>
				<?php endif; ?>
			<?php endfor; ?>
		</tr>
			<?php
			if ( $ended ) {
				break;
			} endfor;
		?>
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
