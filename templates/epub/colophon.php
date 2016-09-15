<?php
/** @var WP_Post $series */
/** @var bool $is_vertical */
/** @var \Hametuha\Rest\EPub $this */
$publisher = get_the_author_meta( '_publisher_name' );
?>
<?php get_template_part( 'templates/epub/header' ) ?>

<div class="header header--colophon">
	<h1 class="title">
		書誌情報
	</h1>
	<div class="meta">
		<div class="date">
			<?php the_date( 'Y年m月d日' ) ?>発行<br />
			<?php the_modified_date( 'Y年m月d日' ) ?>更新
		</div>
	</div>
</div>

<article class="content content--colophon" epub:type="colophon">

	<?php if ( ! $publisher ) : ?>
	<p class="text-center">
		<img src="<?= get_template_directory_uri() ?>/assets/img/hametuha-logo.png" alt="破滅派" width="150" height="75">
	</p>
	<?php endif; ?>

	<table class="colophon">
		<caption>書誌情報</caption>
		<tr>
			<th>書名</th>
			<td><?php the_title() ?></td>
		</tr>
		<tr>
			<th>初出</th>
			<td>
				<a class="url" href="<?php the_permalink() ?>"><?php the_permalink() ?></a>（<?php the_time( 'Y年m月d日' ) ?>）
			</td>
		</tr>
		<tr>
			<th>著者</th>
			<td>
				<?= implode( ' / ', array_map( function( WP_User $author ) {
					return esc_html( $author->display_name );
				}, $authors ) ); ?>
			</td>
		</tr>
		<tr>
			<th>編集者</th>
			<td><?php the_author() ?></td>
		</tr>
		<?php if ( ! $publisher ) : ?>
			<tr>
				<th>発行人</th>
				<td>高橋文樹</td>
			</tr>
			<tr>
				<th>発行所</th>
				<td>
					<a href="<?= home_url( '' ) ?>"><?php bloginfo( 'name' ) ?></a>
				</td>
			</tr>
		<?php endif; ?>
		<?php foreach( [
			'_asin' => 'ASIN',
		] as $key => $label ): if ( ! ( $value = get_post_meta( get_the_ID(), $key, true ) ) ){ continue; } ?>
		<tr>
			<th><?= esc_html( $label ) ?></th>
			<td><?= esc_html( $value ) ?></td>
		</tr>
		<?php endforeach; ?>
	</table>


	<table class="colophon">
		<caption>初出一覧</caption>
		<?php
		$series_query = new \WP_Query(   [
			'post_type' => 'post',
			'post_parent' => get_the_ID(),
			'posts_per_page' => -1,
			'orderby' => [
				'menu_order' => 'DESC',
				'post_date' => 'ASC',
			]
		]);
		if ( $series_query->have_posts() ) :
		?>
			<?php $counter = 0; while( $series_query->have_posts() ) : $series_query->the_post(); $counter++; ?>
			<tr>
				<th><?= number_format( $counter ) ?></th>
				<td>
					<a href="<?php the_permalink() ?>">
						<?php the_title() ?>
					</a>
					<br />
					<small><?php the_time( 'Y年m月d日' ) ?></small>
				</td>
			</tr>
			<?php endwhile; wp_reset_postdata(); ?>
		<?php endif; ?>
	</table>

	<table class="colophon">
		<caption>連絡先</caption>
		<tr>
			<th>名称</th>
			<td><?= esc_html( $publisher ) ?: '株式会社破滅派' ?></td>
		</tr>
		<?php
		$tel = get_the_author_meta( '_publisher_tel' ) ?: '050-5532-8327';
		if ( 'no' !== $tel ) :
			?>
			<tr>
				<th>電話</th>
				<td><?= esc_html( $tel ) ?></td>
			</tr>
			<?php
		endif;
		$mail = get_the_author_meta( '_publisher_mail' ) ?: 'info@hametuha.co.jp';
		if ( 'no' !== $mail ) :
			?>
			<tr>
				<th>メール</th>
				<td><a href="mailto:<?= esc_attr( $mail ) ?>"><?= esc_html( $mail ) ?></a></td>
			</tr>
			<?php
		endif;
		$address = get_the_author_meta( '_publisher_address' ) ?: '〒262-0019 東京都港区南青山2-11-13 南青山ビル4F';
		if ( 'no' !== $address ) :
			?>
			<tr>
				<th>住所</th>
				<td><?= esc_html( $address ) ?></td>
			</tr>
		<?php endif; ?>
	</table>
</article>


<footer class="footer footer--colophon">
	&copy; <?php the_time( 'Y' ) ?> <?= implode( ' / ', array_map( function( WP_User $author ) {
		return esc_html( $author->display_name );
	}, $authors ) ) ?>
	<?php if ( ! $publisher ) : ?>
	 / Hametuha INC.
	<?php endif; ?>
</footer>

<?php get_template_part( 'templates/epub/footer' ) ?>
