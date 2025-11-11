<?php
/** @var WP_Post $series */
/** @var bool $is_vertical */
/** @var \Hametuha\Rest\EPub $this */
$publisher = hametuha_is_secret_book() ? get_the_author_meta( '_publisher_name' ) : '';
?>
<?php get_template_part( 'templates/epub/header' ); ?>

<div class="header header--colophon">
	<h1 class="title">
		書誌情報
	</h1>
	<div class="meta">
		<div class="date">
			<?php the_date( 'Y年m月d日' ); ?>発行<br />
			<?php the_modified_date( 'Y年m月d日' ); ?>更新
		</div>
	</div>
</div>

<article class="content content--colophon" epub:type="colophon">

	<?php if ( ! $publisher ) : ?>
	<p class="text-center">
		<img src="<?php echo get_template_directory_uri(); ?>/assets/img/hametuha-logo.png" alt="破滅派" width="150" height="75">
	</p>
	<?php endif; ?>

	<table class="colophon">
		<caption>書誌情報</caption>
		<tr>
			<th>書名</th>
			<td><?php the_title(); ?></td>
		</tr>
		<?php if ( ! hametuha_is_secret_book() ) : ?>
		<tr>
			<th>URL</th>
			<td>
				<a class="url" href="<?php the_permalink(); ?>"><?php the_permalink(); ?></a>（<?php the_time( 'Y年m月d日' ); ?>）
			</td>
		</tr>
		<?php endif; ?>
		<tr>
			<th>執筆者</th>
			<td>
				<?php
				echo implode( ' / ', array_map( function( WP_User $author ) {
					return esc_html( $author->display_name );
				}, $authors ) );
				?>
			</td>
		</tr>
		<tr>
			<th>編集者</th>
			<td><?php echo esc_html( hametuha_author_name() ); ?></td>
		</tr>
		<?php if ( ! $publisher ) : ?>
			<tr>
				<th>発行人</th>
				<td>高橋文樹</td>
			</tr>
			<tr>
				<th>発行所</th>
				<td>
					<a href="<?php echo home_url( '' ); ?>"><?php bloginfo( 'name' ); ?></a>
				</td>
			</tr>
		<?php endif; ?>
		<?php
		foreach ( [
			'_asin' => 'ASIN',
		] as $key => $label ) :
			if ( ! ( $value = get_post_meta( get_the_ID(), $key, true ) ) ) {
				continue; }
			?>
		<tr>
			<th><?php echo esc_html( $label ); ?></th>
			<td><?php echo esc_html( $value ); ?></td>
		</tr>
		<?php endforeach; ?>
	</table>

	<?php if ( ! get_post_meta( $post->ID, '_hide_correct', true ) ) : ?>
	<table class="colophon">
		<caption>初出一覧</caption>
		<?php
		$series_query = \Hametuha\Model\Series::get_series_posts( get_the_ID(), [ 'publish', 'private' ], true );
		if ( $series_query->have_posts() ) :
			?>
			<?php
			$counter = 0;
			while ( $series_query->have_posts() ) :
				$series_query->the_post();
				$counter++;
				?>
			<tr>
				<th><?php echo number_format( $counter ); ?></th>
				<td>
					<?php if ( $corrected = hametuha_first_collected( true, $post ) ) : ?>
						<?php echo esc_html( get_the_title( $post ) ); ?>
						（<?php echo $corrected; ?>）
					<?php else : ?>
						<a href="<?php the_permalink(); ?>">
							<?php the_title(); ?>
						</a>
						<br/>
						<small><?php the_time( 'Y年m月d日' ); ?></small>
					<?php endif; ?>
				</td>
			</tr>
				<?php
			endwhile;
			wp_reset_postdata();
			?>
		<?php endif; ?>
	</table>
	<?php endif; ?>

	<table class="colophon">
		<caption>連絡先</caption>
		<tr>
			<th>発行者</th>
			<td><?php echo esc_html( $publisher ) ?: '株式会社破滅派'; ?></td>
		</tr>
		<?php
		$tel = '050-5532-8327';
		if ( hametuha_is_secret_book() ) {
			$tel = get_the_author_meta( '_publisher_tel' ) ?: $tel;
		}
		if ( 'no' !== $tel ) :
			?>
			<tr>
				<th>電話</th>
				<td><?php echo esc_html( $tel ); ?></td>
			</tr>
			<?php
		endif;
		$mail = 'info@hametuha.co.jp';
		if ( hametuha_is_secret_book() ) {
			$mail = get_the_author_meta( '_publisher_mail' ) ?: $mail;
		}
		if ( 'no' !== $mail ) :
			?>
			<tr>
				<th>メール</th>
				<td><a href="mailto:<?php echo esc_attr( $mail ); ?>"><?php echo esc_html( $mail ); ?></a></td>
			</tr>
			<?php
		endif;
		$address = '〒104-0061 東京都中央区銀座1-3-3 G1ビル7F 1211号';
		if ( hametuha_is_secret_book() ) {
			$address = get_the_author_meta( '_publisher_address' ) ?: $address;
		}
		if ( 'no' !== $address ) :
			?>
			<tr>
				<th>住所</th>
				<td><?php echo esc_html( $address ); ?></td>
			</tr>
		<?php endif; ?>
	</table>
</article>


<footer class="footer footer--colophon">
	&copy; <?php the_time( 'Y' ); ?> <?php
	echo implode( ' / ', array_map( function( WP_User $author ) {
		return esc_html( $author->display_name );
	}, $authors ) )
	?>
	<?php if ( ! $publisher ) : ?>
	 / Hametuha INC.
	<?php endif; ?>
</footer>

<?php get_template_part( 'templates/epub/footer' ); ?>
