<?php
/**
 * Recommendation template.
 * @var array $args
 */
$args   = wp_parse_args(
	$args,
	[
		'excludes'       => 0,
		'author'         => 0,
		'author_not'     => 0,
		'posts_per_page' => 12,
		'fill'           => false,
	]
);
$series = get_posts(
	hametuha_series_args(
		[
			'excludes'       => $args['excludes'],
			'author'         => $args['author'],
			'posts_per_page' => $args['posts_per_page'],
		]
	)
);
if ( $args['fill'] && count( $series ) < $args['posts_per_page'] ) {
	foreach ( get_posts(
		hametuha_series_args(
			[
				'author_not'     => [ $args['author'] ],
				'posts_per_page' => $args['posts_per_page'] - count( $series ),
			]
		)
	) as $s ) {
		$series[] = $s;
	}
}
if ( ! $series ) : ?>

<div class="alert alert-warning text-center" style="max-width: 600px; margin: 40px auto 0;">
	まだリリースしている電子書籍はありません。
</div>

<?php else : ?>

<ol class="book-recommend">

	<?php
	foreach ( $series as $post ) :
		setup_postdata( $post );
		?>
		<li class="book-recommend-item">
			<a class="book-recommend-link" href="<?php the_permalink(); ?>">

				<?php the_post_thumbnail( 'medium', [ 'class' => 'book-recommend-cover' ] ); ?>

				<span class="book-recommend-title"><?php the_title(); ?></span><br />
				<span class="book-recommend-author"><i class="icon-user"></i> <?php echo esc_html( hametuha_author_name() ); ?></span>
			</a>
		</li>
		<?php
	endforeach;
	wp_reset_postdata();
	?>

</ol>

	<?php
endif;
