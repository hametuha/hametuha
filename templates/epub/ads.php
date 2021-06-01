<?php
/** @var \Hametuha\Model\Series $series */
/** @var int $series_id */
$post       = get_post( $series_id );
$recommends = get_posts(
	hametuha_series_args(
		[
			'excludes'       => $series_id,
			'author'         => $post->post_author,
			'posts_per_page' => 10,
		]
	)
);
if ( count( $recommends ) < 10 ) {
	foreach ( get_posts(
		hametuha_series_args(
			[
				'author_not'     => $post->post_author,
				'posts_per_page' => 10 - count( $recommends ),
			]
		)
	) as $s ) {
		$recommends[] = $s;
	}
}
?>
<?php get_template_part( 'templates/epub/header' ); ?>

<div class="header header--afterwords">
	<h1 class="title">破滅派の電子書籍</h1>

	<div class="header__lead">
		破滅派から刊行されている電子書籍の既刊（<?php echo mb_convert_kana( date_i18n( 'Y年n月現在' ), 'N', 'UTF-8' ); ?>）です。
		すべての作品は<a href="<?php echo home_url( 'kdp' ); ?>">こちら</a>からご覧ください。
	</div>
</div>



<article class="content content--ads content--afterwords clearfix">

	<?php $counter = 0; foreach ( $recommends as $recommend ) : ?>

		<?php $genre = hametuha_get_series_categories( $recommend ); ?>
		<h2 class="ads-title">
			<?php echo get_the_title( $recommend ); ?>
			<small class="ads-category">
				<?php
					echo esc_html( current( $genre )->name );
				if ( 1 < count( $genre ) ) {
					echo 'ほか';
				}
				?>
			</small>
		</h2>
		<p class="ads-author"><?php echo hametuha_author_name( $recommend ); ?></p>
		<blockquote class="ads-lead">
			<?php echo wpautop( get_the_excerpt( $recommend ) ); ?>
			<p class="text-center"><a class="ads-btn" href="<?php echo \Hametuha\Model\Series::get_instance()->get_kdp_url( $recommend->ID ); ?>">アマゾンで見る</a></p>
		</blockquote>

		<?php
		$counter++;
endforeach;
	?>

</article>

<?php get_template_part( 'templates/epub/footer' ); ?>
