<?php
if ( is_tax( 'nouns' ) ) {
	$title   = '関連するキーワード';
	$days    = 0;
	$limit   = 0;
	$term_id = get_queried_object_id();
} else {
	$title   = '最近人気のキーワード';
	$days    = 30;
	$limit   = 20;
	$term_id = 0;
}

if ( $terms = hamenew_popular_nouns( $term_id, $days, $limit ) ) :
	usort( $terms, function( $a, $b ) {
		if ( $a->count == $b->count ) {
			return 0;
		} else {
			return $a->count < $b->count ? 1 : -1;
		}
	} );
	?>
	<hr/>
	<h2 class="news-keywords__title"><?php echo $title; ?></h2>
	<p class="news-keywords__wrapper">
		<?php
		echo implode( ' ', array_map( function ( $term ) {
			return sprintf(
				'<a href="%s" class="news-keywords__link"><i class="icon-tag6"></i> %s(%d)</a>',
				get_term_link( $term ),
				esc_html( $term->name ),
				$term->count > 100 ? '99+' : number_format( $term->count )
			);
		}, $terms ) );
		?>
	</p>
<?php endif; ?>
