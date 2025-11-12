<?php
/*
 * Template Name: ヘルプセンター
 *
 *
 * @feature-group faq
 */

get_template_part( 'templates/faq/header-faq' );
?>

<section class="help-center-latest">

	<div class="container">
		<div class="row justify-content-center">

		<div class="col-12 col-md-8 text-end">

			<h2 class="help-center-latest-title">よくある質問</h2>
			<ul class="help-center-latest-list">
				<?php foreach ( hametuha_popular_faqs() as $faq ) : ?>
					<li class="help-center-latest-item">
						<a href="<?php the_permalink( $faq ); ?>">
							<?php echo esc_html( get_the_title( $faq ) ); ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>

		</div><!-- //.row -->
	</div>

</section>

<section class="help-center-list">
	<div class="container">
		<h2 class="text-center help-center">最新のヘルプ</h2>
		<div class="row">
			<?php
			$posts = get_posts( [
				'post_type'      => 'faq',
				'posts_per_page' => 12,
				'post_status'    => 'publish',
			] );
			for ( $i = 0; $i < 3; $i++ ) :
				?>
				<div class="col-12 col-lg-4">
					<ul class="help-center-list-items">
						<?php
						for ( $j = 0; $j < 4; $j++ ) :
							$p = $posts[ $i * 3 + $j ];
							?>
						<li class="help-center-list-item">
							<a href="<?php the_permalink( $p ); ?>" class="help-center-list-link">
								<?php echo esc_html( get_the_title( $p ) ); ?>
							</a>
						</li>
						<?php endfor; ?>
					</ul>
				</div>
			<?php endfor; ?>
		</div>
	</div>
</section>

<section class="help-center-topic">

<?php
	$terms = get_terms( [ 'taxonomy' => 'faq_cat' ] );
if ( $terms && ! is_wp_error( $terms ) ) :
	?>
<div class="container">

	<h2 class="text-center">トピック</h2>

	<?php
	$rows = [];
	$idx  = 0;
	foreach ( $terms as $index => $term ) {
		$index = (int) floor( $index / 3 );
		if ( ! isset( $rows[ $index ] ) ) {
			$rows[ $index ] = [];
		}
		$rows[ $index ][] = $term;
	}
	foreach ( $rows as $row ) :
		?>
	<div class="row">
		<?php foreach ( $row as $term ) : ?>
		<div class="col-12 col-sm-6 col-md-4">
			<div class="card mb-3">
				<div class="card-body">
					<h3><?php echo esc_html( $term->name ); ?></h3>
					<?php echo wpautop( esc_html( $term->description ) ); ?>
					<p>
						<a href="<?php echo get_term_link( $term ); ?>" class="btn btn-primary" role="button">すべてみる</a>
					</p>
				</div><!-- //.card-body -->
			</div><!-- //.card -->
		</div>
		<?php endforeach; ?>
	</div>
	<?php endforeach; ?>
</div>
</section>
<?php endif; ?>

<section class="help-center-misc">

	<h2 class="help-center-misc-title text-center">
		解決しませんでしたか？
	</h2>
	<p class="help-center-misc-lead text-muted text-center">
		問い合わせると解決することがあります。
	</p>

	<div class="container">
		<div class="row">
		<div class="col-12 col-md-4">
			<div class="help-center-misc-item">
				<p class="text-center">
					<i class="icon-users4"></i>
				</p>
				<h3 class="text-center">コミュニティ</h3>
				<p>
					破滅派には<a href="<?php echo home_url( 'thread' ); ?>">掲示板</a>や<a href="https://hametuha.com/faq/what-is-slack/">Slack</a>などの同人同士で連絡が取れる場所があります。
					お気軽にご利用ください。
				</p>
			</div>
		</div>
		<div class="col-12 col-md-4">
			<div class="help-center-misc-item">
				<p class="text-center">
					<i class="icon-hand"></i>
				</p>
				<h3 class="text-center">リクエスト</h3>
				<p>
					「このような情報を書いてほしい」「これが知りたい」という方はお気軽にリクエストをください。
				</p>
			</div>
		</div>
		<div class="col-12 col-md-4">
			<div class="help-center-misc-item">
				<p class="text-center">
					<i class="icon-phone6"></i>
				</p>
				<h3 class="text-center">問い合わせ</h3>
				<p>
					どうしても解決しない場合は<a href="<?php echo home_url( 'inquiry' ); ?>">お問い合わせ</a>からご連絡ください。
					誠心誠意ご対応いたします。
				</p>
			</div>
		</div>
		</div><!-- //.row -->
	</div>

</section>
<?php
get_footer( 'ebooks' );
get_footer( 'books' );
get_footer();
